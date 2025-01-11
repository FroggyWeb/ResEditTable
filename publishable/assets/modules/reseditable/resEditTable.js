const wrap = document.querySelector("#reseditable");
const url = wrap.dataset.url;
const id = wrap.dataset.id;
const container = wrap.dataset.container;
const token = wrap.dataset.token;

const table = new Tabulator("#reseditable", {
    columns: [
        {
            title: "id",
            field: "id",
            sorter: "number",
            width: 64,
            vertAlign: "middle",
            editor: false,
        },
        {
            title: "",
            field: "image_thumb",
            headerSort: false,
            width: 60,
            formatter: "image",
            vertAlign: "middle",
            formatterParams: {
                urlPrefix: "/",
            },
        },
        {
            title: "Заголовок",
            field: "pagetitle",
            sorter: "string",
            vertAlign: "middle",
            editor: false,
            formatter: titleFormatter,
            headerFilter: "input",
        },

        {
            title: "Популярный товар",
            field: "popular",
            hozAlign: "center",
            vertAlign: "middle",
            headerHozAlign: "center",
            editor: "tickCross",
            editorParams: {
                trueValue: "1",
            },

            formatter: (cell) => {
                const value = cell.getValue();
                const status = value ? "checked" : "";
                return `<input type="checkbox" ${status} />`;
            },
        },
        {
            title: "Стоимость",
            field: "price",
            editor: "number",
            hozAlign: "right",
            sorter: "number",
            vertAlign: "middle",
            headerFilter: minMaxFilterEditor,
            headerFilterFunc: minMaxFilterFunction,
            headerFilterLiveFilter: false,
            headerHozAlign: "center",
        },
        {
            title: "Документ",
            editor: false,
            headerSort: false,
            width: 120,
            hozAlign: "right",
            headerHozAlign: "center",
            vertAlign: "middle",
            formatter: buttonFormatter,
            cellClick: function (e, cell) {
                const data = cell.getData();
                if (e.target.closest(".delDoc")) {
                    const status = data.deleted ? 0 : 1;
                    table
                        .updateData([
                            {
                                id: data.id,
                                deleted: status,
                            },
                        ])
                        .then(storeData(data));
                }
                if (e.target.closest(".pubDoc")) {
                    const status = data.published ? 0 : 1;
                    table
                        .updateData([
                            {
                                id: data.id,
                                published: status,
                            },
                        ])
                        .then(storeData(data));
                }
            },
        },
    ],
    rowFormatter: function (row) {
        const data = row.getData();
        const el = row.getElement();
        data.deleted
            ? el.classList.add("deleted")
            : el.classList.remove("deleted");
        !data.published
            ? el.classList.add("unpublished")
            : el.classList.remove("unpublished");
    },
    index: "id",
    pagination: true,
    paginationSize: 25,
    paginationSizeSelector: [10, 25, 50],
    paginationCounter: "rows",
    paginationButtonCount: 5,
    layout: "fitColumns",
    responsiveLayout: "collapse",
    ajaxURL: url + "/action",
    ajaxConfig: "POST",
    ajaxParams: {
        action: "getRes",
        _token: token,
        id: id,
        container: container,
    },
    locale: "ru-ru",
    langs: {
        "ru-ru": {
            data: {
                loading: "Загрузка", //data loader text
                error: "Ошибка", //data error text
            },
            pagination: {
                page_size: "Строк на странице",
                first: "Первая",
                last: "Последняя",
                prev: "Предыдущая",
                next: "Следующая",
                all: "Все",
                counter: {
                    showing: "Показано",
                    of: "из",
                    rows: "строк",
                },
            },
        },
    },
});

function minMaxFilterEditor(cell, onRendered, success, cancel, editorParams) {
    let end;

    const container = document.createElement("span");

    //create and style inputs
    const start = document.createElement("input");
    start.setAttribute("type", "number");
    start.setAttribute("placeholder", "Min");
    start.setAttribute("min", 0);
    start.setAttribute("max", 100);
    start.style.padding = "4px";
    start.style.width = "50%";
    start.style.boxSizing = "border-box";

    start.value = cell.getValue();

    function buildValues() {
        success({
            start: start.value,
            end: end.value,
        });
    }

    function keypress(e) {
        if (e.keyCode == 13) {
            buildValues();
        }

        if (e.keyCode == 27) {
            cancel();
        }
    }

    end = start.cloneNode();
    end.setAttribute("placeholder", "Max");

    start.addEventListener("change", buildValues);
    start.addEventListener("blur", buildValues);
    start.addEventListener("keydown", keypress);

    end.addEventListener("change", buildValues);
    end.addEventListener("blur", buildValues);
    end.addEventListener("keydown", keypress);

    container.appendChild(start);
    container.appendChild(end);

    return container;
}

//custom max min filter function
function minMaxFilterFunction(headerValue, rowValue, rowData, filterParams) {
    //headerValue - the value of the header filter element
    //rowValue - the value of the column in this row
    //rowData - the data for the row being filtered
    //filterParams - params object passed to the headerFilterFuncParams property

    if (rowValue) {
        rowValue = Number(rowValue);
        if (headerValue.start != "") {
            if (headerValue.end != "") {
                return (
                    rowValue >= headerValue.start && rowValue <= headerValue.end
                );
            } else {
                return rowValue >= headerValue.start;
            }
        } else {
            if (headerValue.end != "") {
                return rowValue <= headerValue.end;
            }
        }
    }

    return true; //must return a boolean, true if it passes the filter.
}

function buttonFormatter(cell) {
    const html = `<a href="index.php?a=27&id=${
        cell.getData().id
    }" target="main" class="docBtn editDoc" title="Редактировать">
                    <i class="fas fa-pen"></i>
                </a>
                <button class="docBtn delDoc" title="Удалить">
                    <i class="fas fa-trash"></i>
                </button>
                <button class="docBtn pubDoc" title="Публиковать">
                    <i class="fas fa-file"></i>
                </button>`;
    return html;
}

function titleFormatter(cell, formatterParams, onRendered) {
    let html = "";
    const isFolder = cell.getData().isfolder;
    const icon = isFolder ? "far fa-folder icon-res" : "far fa-file icon-res";
    html += `<i class="${icon}"></i>`;
    if (!isFolder) {
        html += `<a href="index.php?a=27&id=${
            cell.getData().id
        }" class="res-link" target="main">
                ${cell.getValue()}
                </a>`;
    } else {
        html += `<a href="${url}/show/${container}/${
            cell.getData().id
        }" class="res-link">
                ${cell.getValue()}
                </a>`;
    }
    return html;
}

function storeData(data) {
    fetch(url + "/action", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            // "X-CSRF-Token": token,
        },
        body: JSON.stringify({
            _token: token,
            action: "update",
            data: data,
        }),
    }).then((response) => response.json());
}

table.on("cellEdited", function (cell) {
    let data = cell.getData();
    storeData(data);

    // console.log("cell editing", cell.getData());
});
