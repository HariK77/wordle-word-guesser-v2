<!DOCTYPE html>
<html lang="en">
<?php
// Extract variables with safe defaults
$words = $words ?? [];
$count = $count ?? 0;
$error = $error ?? null;
$success = $success ?? null;
$query = $query ?? [];
$config = config() ?? [];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= esc_attr(config('description')) ?>">
    <title><?= esc_html(config('name')) ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-light bg-light shadow-sm">
        <div class="container">
            <a href="<?= base_url() ?>" class="navbar-brand d-flex align-items-center">
                <span style="font-size: 2rem; margin-right: 10px;">🎮</span>
                <div>
                    <h1 class="mb-0" style="font-size: 1.5rem;"><?= esc_html(config('name')) ?></h1>
                    <small class="text-muted">v<?= config('version') ?></small>
                </div>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="bg-light" style="min-height: 70vh;">
        <div class="container py-4">
            <div class="row mt-4">
                <!-- Flash Messages -->
                <div class="col-12">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i>
                            <?= esc_html($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i>
                            <?= esc_html($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Input Section -->
                <div class="col-lg-6 col-12">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">🎯 Enter Your Clues</h5>
                        </div>
                        <div class="card-body">
                            <!-- Known Letters -->
                            <label for="known" class="form-label fw-bold">🌟 Known Letters</label>
                            <small class="text-muted d-block mb-2">(comma separated)</small>
                            <input
                                type="text"
                                class="form-control mb-4"
                                id="known"
                                placeholder="e.g., A,B,C,D"
                                value="<?= !empty($query['known_letters']) ? esc_attr(implode(',', $query['known_letters'])) : '' ?>">
                            <!-- Letter Inputs -->
                            <label class="form-label fw-bold mb-3">☑️ Position Known Letters</label>
                            <div class="row gap-2 mb-4">
                                <?php for ($i = 1; $i <= ($config['word_length'] ?? 5); $i++): ?>
                                    <div class="col flex-grow-1">
                                        <input
                                            type="text"
                                            class="form-control form-control-lg text-center fw-bold letter-input"
                                            id="l<?= $i ?>"
                                            placeholder="<?= $i ?>"
                                            maxlength="1"
                                            data-position="<?= $i ?>"
                                            value="<?= isset($query['position_known_letters'][$i - 1]) ? esc_attr($query['position_known_letters'][$i - 1]) : '' ?>">
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <!-- Excluded Letters -->
                            <label for="excluded" class="form-label fw-bold">❌ Excluded Letters</label>
                            <small class="text-muted d-block mb-2">(comma separated)</small>
                            <input
                                type="text"
                                class="form-control mb-4"
                                id="excluded"
                                placeholder="e.g., A,B,C,D"
                                value="<?= !empty($query['excluded_letters']) ? esc_attr(implode(',', $query['excluded_letters'])) : '' ?>">

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2 d-sm-flex">
                                <button type="button" class="btn btn-primary btn-lg flex-grow-1" id="guess-btn">
                                    ✨ Guess Word
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-lg" id="clear-btn">
                                    🔄 Clear
                                </button>
                            </div>

                            <!-- Tips -->
                            <div class="alert alert-info mt-4 mb-0">
                                <h6 class="fw-bold">💡 Tips</h6>
                                <ul class="mb-0 small">
                                    <li>Enter at least <strong>1 known letter</strong></li>
                                    <li>Provide <strong>excluded letters</strong> to narrow results faster</li>
                                    <li>Each position can hold <strong>A-Z</strong></li>
                                    <li>Leave blank for <strong>unknown positions</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="col-lg-6 col-12">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">📝 Guessed Words (<?= $count ?>)</h5>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            <?php if ($count === 0): ?>
                                <div class="text-center text-muted py-5">
                                    <p>
                                        <span style="font-size: 2rem;">🤔</span><br>
                                        No words found yet. Try adjusting your clues!
                                    </p>
                                </div>
                            <?php else: ?>
                                <ol class="list-group list-group-numbered">
                                    <?php foreach ($words as $word): ?>
                                        <li class="list-group-item">
                                            <?php if (count($query['known_letters']) > 0): ?>
                                                <strong><?= highlightLetters($word, $query['known_letters']) ?></strong>
                                            <?php else: ?>
                                                <strong><?= $word ?></strong>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        © <?= date('Y') ?> <?= esc_html(config('name')) ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        Environment: <strong><?= config('env') ?></strong>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>

</html>