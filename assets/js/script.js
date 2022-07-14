let $showModulePreview, slicePosition, slice, $modules, $modulePreview, $close, $modulesSearch, $body, $html, previewActive = false, moduleAdded = false, isGridblock = $modulePreviewTabs = false;

$(document).on('rex:ready', function () {

  $showModulePreview = $('.show-module-preview');
  $modulePreview = $('#module-preview');
  $close = $('.nv-modal-header').find('.close');
  $body = $('body');
  $html = $('html');



  hideModulePreview();

  $showModulePreview.off('click');
  //$close.off('click');

  $showModulePreview.on('click', function (event) {
    event.preventDefault();
    slicePosition = $(this).parents('li').attr('id');
    slice = $(this).data('slice');

    if (previewActive) {
      hideModulePreview();
    }
    else {
      showModulePreview($(this));
    }
  });

  $modulePreview.on('click', function (event) {
    const $target = $(event.target);
    if ($target.hasClass('module-list') || $target.parent().hasClass('inner') || $target.attr('id') === 'module-preview') {
      event.preventDefault();
      event.stopPropagation();
      hideModulePreview();
    }
  });



  /**
   * contains case insensitive...
   * https://stackoverflow.com/a/8747204
   */
  jQuery.expr[':'].icontains = function (a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
  };
});

$(document).on('keyup', function (event) {
  if (event.key === 'Escape') hideModulePreview();
});

function showModulePreview(elem) {
  $.ajax({
    url: elem.data('url'),
    beforeSend: function () {
    }
  })
  .done(function (html) {
    if(html) {
      $modulePreview.find('.inner').html(html);

      previewActive = true;
      $modules = $modulePreview.find('a.module');
      if ($modules.length > "1" || $('.nv-copy').length) {
        $modulePreview.fadeIn();
        $body.addClass('module-preview');
        $body.css('height', 'auto');
        $body.css('overflow', 'hidden');
        $html.css('overflow', 'hidden');
        $body.addClass('modal-open');
        $modules.parent().show();
      }

      for (let i = 0; i < $modules.length; i++) {
        if (!$modules.eq(i).data('gridblock')) {
          const href = $modules.eq(i).data('href');
          $modules.eq(i).attr('href', href + '&slice_id=' + slice + '#' + slicePosition);
        }
      }
      attachModuleEventHandler();

      if ($modules.length == "1" && !$('.nv-copy').length) {
        window.location.href=$modules.eq(0).attr('href');
      }

    }
  })
  .fail(function (jqXHR, textStatus, errorThrown) {
    console.error('script.js:89', '  ↴', '\n', jqXHR, textStatus, errorThrown);
  });
}

function attachModuleEventHandler() {

  calcHeight();
  $( window ).resize(function() {
    calcHeight();
  });

  function calcHeight() {
    let offsetHeight = $( window ).outerHeight(); // höhe browserfenster
    let offsetScrollContainer = $('.nv-modal-header').outerHeight();
    let iHeight = offsetHeight-offsetScrollContainer;
    if ($('#nv-modulepreview-tabs').length) {
      iHeight = iHeight-$('#nv-modulepreview-tabs').outerHeight();
    }
    iHeight = iHeight-105;

    $('.nv-scrollable-content-parent').css('height',iHeight+'px');

    $body = $('body');
    $html = $('html');
    if ($('.nv-modal-header').parent('body').hasClass('module-preview')) {
      $('.nv-modal-header').parent('body').css('overflow', 'hidden');
      $('.nv-modal-header').parent('body').css('overflow', 'hidden');
    }
  }

  $modulesSearch = $modulePreview.find('#module-preview-search');

  if ($modulesSearch.length) {
    $modulesSearch.focus();


    $modulePreviewTabs = $('#nv-modulepreview-tabs li a');
    if ($modulePreviewTabs.length) {
      $modulePreviewTabs.on('click', function (event) {
          $modulesSearch.val('');
          searchModules();
          calcHeight();
      })
    }

  }
  $close = $('.nv-modal-header').find('.close');
  $close.on('click', function (event) {
    event.preventDefault();
    hideModulePreview();
  });



  function screenInfo() {
       const showInfo = document.getElementById('showInfo');
       let screenText = document.createTextNode(
          "window.screen.availHeight " + window.screen.availHeight + "\n" + 
          "window.screen.availWidth  " + window.screen.availWidth + "\n" + 
          "window.screen.colorDepth  " + window.screen.colorDepth + "\n" + 
          "window.screen.height      " + window.screen.height + "\n" + 
          "window.screen.pixelDepth  " + window.screen.pixelDepth + "\n" + 
          "window.screen.width       " + window.screen.width);
      console.log(screenText);
 }


  $modules.on('click', function () {
    const $this = $(this);
    moduleAdded = true;
    if ($this.data('gridblock')) {
      isGridblock = true;
    }
    // eslint-disable-next-line no-undef
    const regex = new RegExp('\\bpage=' + rex.page + '(\\b[^/]|$)');
    hideModulePreview();
    $this.prev($('.btn-choosegridmodul')).hide();
/*
    if (regex.test($this.attr('href'))) {
      // event.preventDefault();
      hideModulePreview();
    }*/
  });

  $modulesSearch.on('keyup', function () {
    searchModules();
  });

  function searchModules() {
    const value = $modulesSearch.val();
    if (value) {
      $('.nv-category').hide();
      $modules.parent().hide();
      $modules.filter(':icontains(' + value + ')').parent().show();

      $($modules.filter(':icontains(' + value + ')')).each(function( index ) {
        $('#'+$(this).data('category')+' .nv-category').show();
      });
    }
    else {
      $modules.parent().show();
      $('.nv-category').show();
    }
  }

  /**
   * trap tabbable elements
   */
  const $tabbableElements = $modulePreview.find('select, input, textarea, button, a');
  const $firstTabbableElement = $tabbableElements.first();
  const $lastTabbableElement = $tabbableElements.last();

  $lastTabbableElement.on('keydown', function (e) {
    if ((e.which === 9 && !e.shiftKey) && previewActive) {
      e.preventDefault();
      $firstTabbableElement.focus();
    }
  });

  $firstTabbableElement.on('keydown', function (e) {
    if ((e.which === 9 && e.shiftKey) && previewActive) {
      e.preventDefault();
      $lastTabbableElement.focus();
    }
  });
}

function hideModulePreview() {
  $modulePreview.fadeOut(function () {
    previewActive = false;
    $body.removeClass('module-preview');
    $body.css('height', '100%');
    $body.css('overflow', 'initial');
    $html.css('overflow', 'initial');
    $body.removeClass('modal-open');
    $modulePreview.find('.inner').empty();

    if (moduleAdded) {
      setTimeout(function () {
        if ($('#REX_FORM').length) {
          var dst = $('#REX_FORM');

            pos = dst.offset();
            posTop = parseFloat(pos.top);
            
            if (!isGridblock && posTop > 0) { $("body, html").animate({scrollTop: posTop-80}, 300); }
          moduleAdded = false;
        }
      }, 10)
    }
  });
}
