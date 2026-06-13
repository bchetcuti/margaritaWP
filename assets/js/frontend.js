(function($){
    function esc(text) {
        return $('<div>').text(text == null ? '' : text).html();
    }

    function titleCase(text) {
        text = text || '';
        return text.charAt(0).toUpperCase() + text.slice(1);
    }

    function ingredientRows(d) {
        var sfx = d.suffix;
        var rows = [];
        var tequilaLabel = (d.quantities.tequila && d.quantities.tequila.label) ? d.quantities.tequila.label : 'Tequila';
        if (d.quantities.tequila && d.quantities.tequila.ml > 0) {
            rows.push([tequilaLabel, d.quantities.tequila.display + ' ' + sfx]);
        }
        rows.push(['Citrus juice', d.quantities.citrus.display + ' ' + sfx]);
        if (d.quantities.triple && d.quantities.triple.ml > 0) {
            rows.push(['Triple sec', d.quantities.triple.display + ' ' + sfx]);
        }
        if (d.quantities.agave && d.quantities.agave.ml > 0) {
            rows.push(['Agave syrup', d.quantities.agave.display + ' ' + sfx]);
        }
        if (d.quantities.flavour && d.quantities.flavour.ml > 0) {
            rows.push([d.quantities.flavour.label, d.quantities.flavour.display + ' ' + sfx]);
        }
        return rows;
    }

    function copyText(d) {
        var lines = ['Margarita Recipe'];
        lines.push('Preset: ' + (d.preset_label || titleCase(d.preset)));
        if (d.flavour && d.flavour.key !== 'none') {
            lines.push('Flavour: ' + d.flavour.label);
        }
        lines.push(d.mode === 'pitcher' ? 'Pitcher: ' + d.pitcher_ml + ' ml total' : 'Drinks: ' + d.drinks);
        lines.push('Ingredients:');
        ingredientRows(d).forEach(function(row){ lines.push('- ' + row[0] + ': ' + row[1]); });
        if (typeof d.abv !== 'undefined') {
            lines.push('Estimated ABV: ' + d.abv + '%');
        }
        if (d.salt_rim) {
            lines.push('Salt rim: ~' + d.salt_rim.grams + 'g (' + d.salt_rim.tsps + ' tsp) — ' + d.salt_rim.wet_dry + ' rim');
        }
        lines.push('Made with Margarita Measurements for WordPress');
        return lines.join('\n');
    }

    function writeClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function(resolve, reject){
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', 'readonly');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                if (document.execCommand('copy')) { resolve(); } else { reject(); }
            } catch (e) {
                reject(e);
            }
            document.body.removeChild(textarea);
        });
    }

    $(document).on('change', 'select[name="mode"]', function(){
        $(this).closest('.mm-form').find('.mm-pitcher-row').toggle( $(this).val() === 'pitcher' );
    });

    $(document).on('submit', '.mm-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var $results = $form.siblings('.mm-results');
        $results.html('<div class="mm-loading" role="status" aria-live="polite">Calculating…</div>');

        var data = $form.serializeArray().reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        data.action = 'mm_calculate';
        data.nonce  = MM_Ajax.nonce;

        if (data.mode === 'pitcher') {
            delete data.drinks;
        }

        $.post(MM_Ajax.ajax_url, data, function(resp){
            if (!resp || !resp.success) {
                $results.html('<div class="mm-error">Something went wrong. Please try again.</div>');
                return;
            }
            var d = resp.data;
            $results.data('mm-result', d);
            var title = d.mode === 'pitcher'
                ? 'Pitcher (' + esc(d.pitcher_ml) + ' ml total) — ' + esc(d.preset_label || titleCase(d.preset))
                : 'Batch for ' + esc(d.drinks) + ' drink(s) — ' + esc(d.preset_label || titleCase(d.preset));
            if (d.flavour && d.flavour.key !== 'none') {
                title += ' · ' + esc(d.flavour.label);
            }
            var html = '';
            html += '<div class="mm-card">';
            html += '<h3>' + title + '</h3>';
            html += '<ul>';
            ingredientRows(d).forEach(function(row){
                html += '<li><strong>' + esc(row[0]) + ':</strong> ' + esc(row[1]) + '</li>';
            });
            html += '</ul>';
            if (d.flavour && d.flavour.heat_modifier) {
                html += '<p class="mm-note">🌶 Heat-friendly flavour: jalapeño tequila assumed.</p>';
            }
            if (typeof d.abv !== 'undefined') {
                html += '<p><em>Estimated ABV:</em> ' + esc(d.abv) + '%</p>';
            } else if (d.flavour && d.flavour.no_alcohol) {
                html += '<p><em>Alcohol-free:</em> no ABV estimate shown.</p>';
            }
            if (d.salt_rim) {
                html += '<p>🧂 <strong>Salt rim:</strong> ~' + esc(d.salt_rim.grams) + 'g (' + esc(d.salt_rim.tsps) + ' tsp) — ' + esc(d.salt_rim.wet_dry) + ' rim</p>';
            }
            html += '<div class="mm-actions">';
            html += '<button class="mm-print mm-btn-secondary" type="button">Print</button>';
            html += '<button class="mm-copy mm-btn-secondary" type="button">Copy</button>';
            html += '</div>';
            html += '</div>';
            $results.html(html);
        }).fail(function(){
            $results.html('<div class="mm-error">Network error. Please try again.</div>');
        });
    });

    $(document).on('click', '.mm-copy', function(){
        var $btn = $(this);
        var d = $btn.closest('.mm-results').data('mm-result');
        if (!d) { return; }
        var original = $btn.text();
        writeClipboard(copyText(d)).then(function(){
            $btn.text('✓ Copied!');
            window.setTimeout(function(){ $btn.text(original); }, 2000);
        });
    });

    $(document).on('click', '.mm-print', function(){
        var d = $(this).closest('.mm-results').data('mm-result');
        if (!d) { return; }
        var tableRows = ingredientRows(d).map(function(r){ return '<tr><td><strong>' + esc(r[0]) + '</strong></td><td>' + esc(r[1]) + '</td></tr>'; }).join('');
        var saltLine = d.salt_rim ? '<tr><td><strong>Salt (rim)</strong></td><td>~' + esc(d.salt_rim.grams) + 'g (' + esc(d.salt_rim.tsps) + ' tsp)</td></tr>' : '';
        var modeLabel = d.mode === 'pitcher' ? 'Pitcher — ' + esc(d.pitcher_ml) + ' ml total' : esc(d.drinks) + ' drink(s)';
        var flavourLabel = d.flavour && d.flavour.key !== 'none' ? ' · ' + esc(d.flavour.label) : '';
        var html = '<div id="mm-print-frame">'
            + '<h2>🍋 Margarita Recipe</h2>'
            + '<p class="mm-print-meta">' + esc(d.preset_label || titleCase(d.preset)) + flavourLabel + ' · ' + modeLabel + '</p>'
            + '<table><tbody>' + tableRows + saltLine + '</tbody></table>'
            + (typeof d.abv !== 'undefined' ? '<p>Estimated ABV: <strong>' + esc(d.abv) + '%</strong></p>' : '')
            + '<p class="mm-print-footer">Generated by Margarita Measurements · ' + esc(new Date().toLocaleDateString()) + '</p>'
            + '</div>';
        $('#mm-print-frame').remove();
        $('body').append(html);
        window.print();
        $('#mm-print-frame').remove();
    });
})(jQuery);
