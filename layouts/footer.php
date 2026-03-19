<?php

$bottom_scripts = '
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
    <script defer>
        $(document).ready(function() {
            if ($(".select2").length > 0) {
                $(".select2").select2({
                   width: "100%",
                   dropdownParent: $(".modal-body")
                });
            }
            
            if ($(".datatable").length > 0) {
                // $(".datatable").DataTable();
                new DataTable(".datatable");
            }
        });
    </script>
';

return [
    'bottom_scripts' => $bottom_scripts
];


  
