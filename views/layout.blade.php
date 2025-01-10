<?php include_once MODX_MANAGER_PATH . 'includes/header.inc.php'; ?>
<div class="directory-page">
  <h1>
    <i class="fa fa-list"></i>
    {{ $folder ? $folder->pagetitle : $container->pagetitle }}
  </h1>
  @yield('buttons')

  <div class="sectionBody">

    <div class="tab-pane" id="documentPane">

      <div class="tab-page" id="tab_main">
        <h2 class="tab">
          {{ $lang['documents_list'] }}
        </h2>

        @if (!empty($crumbs))
          <div class="crumbs">
            <ul>
              @foreach ($crumbs as $crumb)
                @if ($loop->last)
                  <li class="current-crumb">
                    {{ $crumb->pagetitle }}
                  @else
                  <li class="crumb">
                    <a
                      href="{{ route('resedittable::show', ['container' => $config['id'], 'folder' => $crumb->id != $container->id ? $crumb->id : null]) }}">
                      @if ($loop->first)
                        <i class="fa fa-home"></i>
                      @else
                        {{ $crumb->pagetitle }}
                      @endif
                    </a>
                @endif
              @endforeach
            </ul>
          </div>
        @endif

        @yield('body')
        @csrf()

        <div id="reseditable" data-url="{{ route('resedittable::index') }}"
          data-id='{{ $folder ? $folder->id : $container->id }}' data-container={{ $container->id }}
          data-token='{{ csrf_token() }}'>
        </div>
      </div>

    </div>
  </div>

  @stack('scripts')

  <link rel="stylesheet" href="{{ MODX_BASE_URL }}assets/modules/reseditable/tabulator/css/tabulator.min.css">
  <link rel="stylesheet" href="{{ MODX_BASE_URL }}assets/modules/reseditable/resEditTable.css">

  <script src="{{ MODX_BASE_URL }}assets/modules/reseditable/tabulator/js/luxon.min.js"></script>
  <script src="{{ MODX_BASE_URL }}assets/modules/reseditable/tabulator/js/tabulator.min.js"></script>
  <script src="{{ MODX_BASE_URL }}assets/modules/reseditable/resEditTable.js"></script>

  <?php include_once MODX_MANAGER_PATH . 'includes/footer.inc.php'; ?>
