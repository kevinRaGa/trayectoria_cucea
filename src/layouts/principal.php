<!DOCTYPE html>
<html lang="es-MX">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title><?= $title ?? 'Trayectoria CUCEA' ?></title>
   <meta name="description" content="<?= $description ?? '' ?>">
   <meta name="author" content="Trayectoria CUCEA">

   <link rel="stylesheet" href="./assets/css/globals.css">
   <?= $css ?? '' ?>
</head>

<body>
   <?= $content ?>
   <?= $js ?? '' ?>
</body>

</html>