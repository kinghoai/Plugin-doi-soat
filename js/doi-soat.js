jQuery(document).ready(function() {
    let objParams = getSearchParams();
        function getSearchParams(k){
            var p={};
            location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(s,k,v){p[k]=decodeURIComponent(v);})
            return k?p[k]:p;
        }

        let objOrder_status = objParams.order_status;
        let objHang_co_san = objParams.hang_co_san;
        let objOrder_id = objParams.order_id;
        let objStartdate = objParams.start_date;
        let objEnddate = objParams.end_date;


        jQuery("#order_statuses_doi_soat option[value='"+objOrder_status+"']").attr("selected","selected");
        jQuery("#hang_co_san_doi_soat option[value='"+objHang_co_san+"']").attr("selected","selected");
        jQuery("#search_id_order").attr("value",objOrder_id);
        jQuery("#start_date").attr("value",objStartdate);
        jQuery("#end_date").attr("value",objEnddate);

    jQuery("#doi-soat-filter").click(function() {
        var orderStatus = jQuery("#order_statuses_doi_soat option:selected").val();
        var newParams = objParams;
            filterOrderParams = {order_status:orderStatus }
            var newParams = Object.assign({}, newParams, filterOrderParams);
        var hangCoSan = jQuery("#hang_co_san_doi_soat option:selected").val();
            filterHangCoSanParams = {hang_co_san:hangCoSan }
            var newParams = Object.assign({}, newParams, filterHangCoSanParams);
        var idOrder = jQuery("#search_id_order").val();
            searchIdOrderParams = {order_id:idOrder }
            var newParams = Object.assign({}, newParams, searchIdOrderParams);
        var startDate = jQuery("#start_date").val();
            startDateParams = { start_date:startDate }
            var newParams = Object.assign({}, newParams, startDateParams);
        var endDate = jQuery("#end_date").val();
            endDateParams = { end_date:endDate }
            var newParams = Object.assign({}, newParams, endDateParams);
        
    console.log(newParams);
    window.location.href = window.location.protocol + '//' + window.location.host + window.location.pathname + '?' + jQuery.param(newParams);
    });

    jQuery("#reset-filter").click(function() {
        var resetParams = {page:"doi_soat"}
    window.location.href = window.location.protocol + '//' + window.location.host + window.location.pathname + '?' + jQuery.param(resetParams);
    });



});