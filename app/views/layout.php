<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="/public/css/bootstrap.css">

    <title><?php echo $title; ?></title>
</head>
<body>

    <header class="navbar navbar-light bg-light">
        <div class="container">
            <div class="row">
                <nav class="col-12">

                    <?php include('menu.php') ?>

                </nav>
            </div>
        </div>
    </header>

    <main class="mt-5">
        <div class="container">
            <div class="row">

                <?php echo $content; ?>

            </div>
        </div>
    </main>


    <script src="/public/js/jquery-3.3.1.min.js"></script>
    <script src="/public/js/bootstrap.js"></script>

    
</body>
</html>