<?php
// Memulai buffering
ob_start();
// include __DIR__ . '/../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Mengakhiri buffering
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5 py-4 py-md-6">
        <div class="row mb-5">
            <div class="col-md-6 align-self-center">
                <div class="lc-block text-center">
                    <img class="img-fluid mb-4" src="https://cdn.livecanvas.com/media/svg/undraw/analytics.svg" style=""
                        loading="lazy" width="350" height="350">
                </div><!-- /lc-block -->
            </div><!-- /col -->
            <div class="col-md-6">
                <div class="lc-block">
                    <div editable="rich">
                        <h2>The quick brown fox jumps over the lazy cat</h2>
                        <form>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="floatingInput"
                                    placeholder="name@example.com">
                                <label for="floatingInput">Email address</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="floatingPassword"
                                    placeholder="Password">
                                <label for="floatingPassword">Password</label>
                            </div>
                            <div class="d-grid gap-2 col-6 mx-auto mt-4">
                                <button class="btn btn-primary" type="button">Button</button>
                            </div>
                        </form>

                        <p><br></p>
                    </div>
                </div><!-- /lc-block -->
            </div><!-- /col -->
        </div>
        <div class="row mt-4">
            <div class="col-md-3 col-sm-6 text-center">
                <div class="lc-block">
                    <img class="img-fluid mb-3" src="https://cdn.livecanvas.com/media/svg/undraw/tweetstorm.svg"
                        loading="lazy" width="92" height="92" style="height:10vh">
                </div>
                <div class="lc-block">
                    <div editable="rich">

                        <h4>The quick brown</h4>

                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.&nbsp;</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="lc-block"><img class="img-fluid mb-3"
                        src="https://cdn.livecanvas.com/media/svg/undraw/playful-cat.svg" loading="lazy" width="92"
                        height="92" style="height:10vh"></div>
                <div class="lc-block">
                    <div editable="rich">

                        <h4>The quick brown</h4>

                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.&nbsp;</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="lc-block"><img class="img-fluid mb-3"
                        src="https://cdn.livecanvas.com/media/svg/undraw/broadcast.svg" loading="lazy" width="92"
                        height="92" style="height:10vh"></div>
                <div class="lc-block">
                    <div editable="rich">

                        <h4>The quick brown</h4>

                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.&nbsp;</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="lc-block"><img class="img-fluid mb-3"
                        src="https://cdn.livecanvas.com/media/svg/undraw/android.svg" loading="lazy" width="92"
                        height="92" style="height:10vh"></div>
                <div class="lc-block">
                    <div editable="rich">

                        <h4>The quick brown</h4>

                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.&nbsp;</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>