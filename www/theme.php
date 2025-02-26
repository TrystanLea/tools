<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="#" style="margin-left:10px">OpenEnergyMonitor.org Tools</a>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
        <div class="offcanvas-header">
            <h5 id="sidebarLabel">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="list-group">
                <?php foreach ($menu as $key => $value): ?>
                    <?php if (isset($value['hide']) && $value['hide'] === true) continue; ?>
                    <a href="<?php echo $value['case']; ?>" class="list-group-item list-group-item-action"><?php echo $value['title']; ?></a>
                <?php endforeach; ?>
            </div>

            <h5 class="mt-3">Other tools</h5>
            <div class="list-group mt-3">
                <a href="https://openenergymonitor.org/heatlossjs" class="list-group-item list-group-item-action">HeatLoss.js</a>
                <a href="https://openenergymonitor.org/sapjs" class="list-group-item list-group-item-action">SAP.js</a>
                <a href="https://openenergymonitor.org/zcem" class="list-group-item list-group-item-action">ZeroCarbonBritain energy model</a>
            </div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <main class="flex-grow-1">
        <?php echo $content; ?>
    </main>

    <footer class="bg-light text-center text-lg-start mt-4">
        <div class="text-center p-3" style="background-color:whitesmoke">
            <a class="text-dark" href="<?php echo $github; ?>">Open Source on Github</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>


</html>

