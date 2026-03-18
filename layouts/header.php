<?php

$system_title = "Client Directory";

$top_scripts = '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
';

$navbar = '
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="/">' . $system_title . '</a>
                <div class="navbar-nav">
                    <a class="nav-link" href="/">Clients</a>
                    <a class="nav-link" href="/contacts">Contacts</a>
                </div>
            </div>
        </nav>
';


  return [
    'system_title' => $system_title,
    'top_scripts' => $top_scripts,
    'navbar' => $navbar
];
