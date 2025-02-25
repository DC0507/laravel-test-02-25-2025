require('./bootstrap');
$('.toggle').on('click', function(){
    var $a = $(this);
    var $row = $($a.attr('href'));
    $row.is(':visible') ? $row.fadeOut() : $row.fadeIn();
})
