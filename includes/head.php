<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Equipment Tracker'); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-M5QxQ1oLeG1JnWlD6TfoZQJ/7Q+BlLI96d3LszAXMsXgSUs7eUQV9ehZaZi7niNW" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <?php if (!empty($pageStyles)): ?>
        <?php echo $pageStyles; ?>
    <?php endif; ?>
</head>
