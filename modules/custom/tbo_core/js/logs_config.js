/**
 * @file
 * Custom scripts for audit logs.
 */
(function ($) {

    Drupal.behaviors.auditLogsBehavior = {
        attach: function (context, drupalSettings) {
            var config = drupalSettings.options_date;
            if (config != 'undefined') {
                calendar_logs(config);
            }
            function calendar_logs(config) {
                var date_range = parseInt(config.options_date);
                var from_input = $('#date_start_log').pickadate({
                        max: true,
                        format: 'mmmm dd, yyyy'
                    }),
                    from_picker = from_input.pickadate('picker');
                var to_input = $('#date_end_log').pickadate({
                        max: true,
                        format: 'mmmm dd, yyyy'
                    }),
                    to_picker = to_input.pickadate('picker');
                // Check if there’s a “from” or “to” date to start with.
                if (from_picker.get('value')) {
                    to_picker.set('min', from_picker.get('select'))
                }
                if (to_picker.get('value')) {
                    from_picker.set('max', to_picker.get('select'))

                }
                // When something is selected, update the “from” and “to” limits.
                from_picker.on('set', function (event) {
                    if (event.select) {
                        var fecha_objeto = (from_picker.get('select')).obj;
                        var ano = (this.get('select')).year;
                        var mes = (this.get('select')).month;
                        var dia = ((this.get('select')).date)/*+date_range*/;
                        var dateTimeEnd = Date.now(from_picker);
                        var finalDate = new Date(from_picker.get('select').obj);
                        finalDate.setDate(finalDate.getDate() + date_range);
                        to_picker.set('min', from_picker.get('select'));
                        to_picker.set('max', finalDate);
                    }
                    else if ('clear' in event) {
                        to_picker.set('min', false);
                        to_picker.set('max', true);
                    }
                });
                to_picker.on('set', function (event) {
                    if (event.select) {

                        //reset picker
                        picker.set('max', false);
                    }
                    else if ('clear' in event) {
                        from_picker.set('max', true)
                    }
                });
            }

        }
    }

})(jQuery);
