;(function($){
    $(document).ready(function(){      
        
        $('#stock_status_all_products').change(function(){
            if(this.checked) {                  
                $("#stock_status_selected_categories").val([]).trigger('change'); 
                $("#stock_status_selected_products").val([]).trigger('change');              
            }
        });
        
         
        $('#stock_status_selected_products').change(function(){
            var selected_products = $(this).val();
            if(selected_products.length > 0){                
                $("#stock_status_selected_categories").val([]).trigger('change');
                $('#stock_status_all_products').prop('checked', false).trigger('change');
            }   
        });   

        $('#stock_status_selected_categories').change(function(){
            var selected_cats = $(this).val();
            if(selected_cats.length > 0){                
                $("#stock_status_selected_products").val([]).trigger('change');
                $('#stock_status_all_products').prop('checked', false).trigger('change');
            }  
        });  
        
        // Schedule stock management system
        
        var $checkbox = $('#is_schedule');
        var $dateField = $('#stock_change_date').closest('tr');
        var $timeField = $('#stock_change_time').closest('tr');

        function toggleFields() {
            if ($checkbox.is(':checked')) {
                $dateField.show();
                $timeField.show();
            } else {
                $dateField.hide();
                $timeField.hide();
            }
        }

        toggleFields();

        $checkbox.on('change', function() {
            toggleFields();
        });

        // Assign datepicker

        $('#stock_change_date').datepicker({
            dateFormat: 'yy-M-dd'
        });
    });
})(jQuery);