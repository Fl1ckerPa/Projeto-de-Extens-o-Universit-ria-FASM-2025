<?php
// $title: string, $messages: string[] (mensagens), $type: 'success' | 'error'
if (!isset($title)) { $title = 'Mensagem'; }
if (!isset($messages)) { $messages = []; }
if (!isset($type)) { $type = 'success'; }
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="h4 mb-3"><?= htmlspecialchars($title) ?></h1>
            <?php if (!empty($messages)): ?>
              <div class="alert <?= $type === 'error' ? 'alert-danger' : 'alert-success' ?>" role="alert">
                <ul class="mb-0">
                  <?php foreach ($messages as $msg): ?>
                    <li><?= $msg ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
            <div class="mt-3 d-flex gap-2">
              <?php if (isset($links) && is_array($links)): ?>
                <?php foreach ($links as $href => $label): ?>
                  <a class="btn btn-primary" href="<?= htmlspecialchars($href) ?>"><?= htmlspecialchars($label) ?></a>
                <?php endforeach; ?>
              <?php else: ?>
                <a class="btn btn-outline-secondary" href="javascript:history.back()">Voltar</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
