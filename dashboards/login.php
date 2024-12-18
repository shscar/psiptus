<?php

include __DIR__ . '/../layouts/master.php';
$db = Database::getInstance()->getConnection();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <!--  <link rel="stylesheet" id="picostrap-styles-css" href="https://cdn.livecanvas.com/media/css/library/bundle.css" media="all"> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/livecanvas-team/ninjabootstrap/dist/css/bootstrap.min.css"
        media="all"> -->

</head>

<body>

    <div class="container-fluid px-4 py-5 my-5 text-center">
        <div class="lc-block mb-4">
            <div editable="rich">
                <h2 class="display-2 fw-bold">Level up your <span class="text-primary">WordPress page!</span></h2>
            </div>
        </div>
        <div class="lc-block col-lg-6 mx-auto mb-5">
            <div editable="rich">

                <p class="lead">Quickly design and customize responsive mobile-first sites with Bootstrap</p>
            </div>
        </div>

        <div class="lc-block d-grid gap-2 d-sm-flex justify-content-sm-center mb-5"> <a
                class="btn btn-primary btn-lg px-4 gap-3" href="#" role="button">Try it free</a>
            <a class="btn btn-outline-secondary btn-lg px-4" href="#" role="button">Learn more</a>
        </div>
        <div class="lc-block d-grid gap-2 d-sm-flex justify-content-sm-center">
            <img class="img-fluid"
                src="https://lclibrary.b-cdn.net/starters/wp-content/uploads/sites/15/2021/10/undraw_going_up_ttm5.svg"
                width="" height="783" srcset="" sizes="" alt="">
        </div>
    </div>


    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
        </script>

</body>

</html>