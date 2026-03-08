<script>
$(document).ready(function()
{
    $.ajax(
    {
        post: "GET",
        url: "https://www.wellnesstrade.cz/admin/controllers/stores/product-edit?id=<?= $id ?>&spamallbox=<?= $_POST['spamallbox'] ?>&saunahousebox=<?= $_POST['saunahousebox'] ?>&oldean=<?= $oldean ?>&<?= $http ?>"
    }).done(function()
    {
        alert('aaa');
    }).fail(function()
    {
        alert('neee');
    });

});
</script>
