console.log(1223)

jQuery(document).ready(function() {
    var $ = jQuery.noConflict();


    dataTable = $("#primavera").DataTable({
        aLengthMenu: [
            [25, 50, 100, 200, 500, -1],
            [25, 50, 100, 200, 500, "All"]
        ],
        "paging": false,

    });


    $('#import').on( 'keyup', function () {
        dataTable
            .columns( 0 )
            .search( this.value )
            .draw();
    } );



    $('#ean').on( 'keyup', function () {
        dataTable
            .columns( 1 )
            .search( this.value )
            .draw();
    } );

    $('#name').on( 'keyup', function () {
        dataTable
            .columns( 2 )
            .search( this.value )
            .draw();
    } );

    $('#anavibility').on( 'keyup', function () {
        dataTable
            .columns( 3 )
            .search( this.value )
            .draw();
    } );

    $('#price').on( 'keyup', function () {
        dataTable
            .columns( 4 )
            .search( this.value )
            .draw();
    } );



        let selectButton = $('#select-checkboxes')
    let unselectButton = $('#unselect-checkboxes')


    selectButton.on('click', function (event){
        event.preventDefault();
        console.log('seelct all')

        $('tr td.sorting_1 input').each(function(index, item){

            $(item).prop('checked', true);
            console.log(item)
        })
    });

    unselectButton.on('click', function (event){
        event.preventDefault();
        console.log('unselect all')

        $('tr td.sorting_1 input').each(function(index, item){

            console.log(item)
            $(item).prop('checked', false);
        })
    });



    console.log('test')
    // /*dataTable.columns().every( function () {
    //       var that = this;
    //
    //       $('input', this.footer()).on( 'keyup change', function () {
    //           if ( that.search() !== this.value ) {
    //               that.search(this.value).draw();
    //           }
    //       });
    //     });*/

    //
    // $('.filter-checkbox').on('change', function(e){
    //     var searchTerms = []
    //     $.each($('.filter-checkbox'), function(i,elem){
    //         if($(elem).prop('checked')){
    //             searchTerms.push("^" + $(this).val() + "$")
    //         }
    //     })
    //     dataTable.column(1).search(searchTerms.join('|'), true, false, true).draw();
    // });

    // $('.status-dropdown').on('change', function(e){
    //     var status = $(this).val();
    //     $('.status-dropdown').val(status)
    //     console.log(status)
    //     //dataTable.column(6).search('\\s' + status + '\\s', true, false, true).draw();
    //     dataTable.column(7).search(status).draw();
    // })

});
