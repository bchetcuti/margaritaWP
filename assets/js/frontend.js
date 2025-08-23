(function($){
    $(document).on('submit', '.mm-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var $results = $('#mm-results');
        $results.html('<div class="mm-loading" role="status" aria-live="polite">Calculating…</div>');

        var data = $form.serializeArray().reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        data.action = 'mm_calculate';
        data.nonce  = MM_Ajax.nonce;

        $.post(MM_Ajax.ajax_url, data, function(resp){
            if (!resp || !resp.success) {
                $results.html('<div class="mm-error">Something went wrong. Please try again.</div>');
                return;
            }
            var d = resp.data;
            var sfx = d.suffix;
            var html = '';
            html += '<div class="mm-card">';
            html += '<h3>Batch for ' + d.drinks + ' drink(s) — ' + d.preset.charAt(0).toUpperCase()+d.preset.slice(1) + '</h3>';
            html += '<ul>';
            html += '<li><strong>Tequila:</strong> ' + d.quantities.tequila.display + ' ' + sfx + '</li>';
            html += '<li><strong>Citrus juice:</strong> ' + d.quantities.citrus.display + ' ' + sfx + '</li>';
            if (d.quantities.triple.ml > 0) {
                html += '<li><strong>Triple sec:</strong> ' + d.quantities.triple.display + ' ' + sfx + '</li>';
            }
            if (d.quantities.agave.ml > 0) {
                html += '<li><strong>Agave syrup:</strong> ' + d.quantities.agave.display + ' ' + sfx + '</li>';
            }
            html += '</ul>';
            if (typeof d.abv !== 'undefined') {
                html += '<p><em>Estimated ABV:</em> ' + d.abv + '%</p>';
            }
            html += '<div class="mm-actions">';
            html += '<button class="mm-print" type="button">Print</button>';
            html += '<button class="mm-copy" type="button">Copy</button>';
            html += '</div>';
            html += '</div>';
            $results.html(html);

            $results.find('.mm-copy').on('click', function(){
                var text = $results.text();
                navigator.clipboard.writeText(text).then(function(){ alert('Copied!'); });
            });
            $results.find('.mm-print').on('click', function(){ window.print(); });
        }).fail(function(){
            $results.html('<div class="mm-error">Network error. Please try again.</div>');
        });
    });
})(jQuery);
