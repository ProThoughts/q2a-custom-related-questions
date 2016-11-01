$(function(){
  $('.ias-spinner').hide();
  if($(".qa-qlist-recent .qa-q-list").length && $(".qa-page-links-list").length) {
    if (material_lite) {
      var ias = $(".mdl-layout__content").ias({
        container: ".qa-qlist-recent .qa-q-list"
        ,item: ".qa-q-list-item"
        ,pagination: ".qa-page-links"
        ,next: ".qa-page-next"
        ,delay: 600
      });
    } else {
        var ias = $.ias({
            container: ".qa-qlist-recent .qa-q-list"
            ,item: ".qa-q-list-item"
            ,pagination: ".qa-page-links-list"
            ,next: ".qa-page-next"
            ,delay: 600
        });
        ias.extension(new IASSpinnerExtension());
    }
    ias.extension(new IASTriggerExtension({
        text: "続きを読む",
        textPrev: "前を読む",
        offset: 100,
    }));
    ias.extension(new IASNoneLeftExtension({
        html: '<div class="ias_noneleft">最後の記事です</div>', // optionally
    }));
    ias.on('load', function() {
        $('.ias-spinner').show();
    });
    ias.on('noneLeft', function() {
        $('.ias-spinner').hide();
    });
  }
});