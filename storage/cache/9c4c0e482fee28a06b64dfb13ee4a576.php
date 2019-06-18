
<?php \App\Section::start('content'); ?>

    <?php /* SVG PATHS */ ;?>

    <?php /* M - moveto */ ;?>
        <?php /* L - lineto */ ;?>
        <?php /* Z - closepath */ ;?>
        <?php /* H - Horizontal lineto */ ;?>
        <?php /* V - vertical lineto */ ;?>
        <?php /* C - curveto */ ;?>
        <?php /* S - smooth curveto */ ;?>

    <div class="container">
        <?php /* <svg width="600" height="600"> */ ;?>
            <?php /* <rect x="300" y="100" fill="blue" width="100" height="200"></rect> */ ;?>
            <?php /* <circle cx="200" cy="200" r="50" fill="pink" stroke="red" stroke-width="2"></circle> */ ;?>
            <?php /* <line x1="100" y1="100" x2="120" y2="300" stroke="grey" stroke-width="2"></line> */ ;?>

            <?php /* <path d="M 150 50 L 75 200 L 225 200 C 225 200 150 150 150 50" fill="orange"></path> */ ;?>
            <?php /* <circle cx="150" cy="150" r="5" fill="grey"></circle> */ ;?>
            <?php /* <line x1="225" y1="200" x2="150" y2="150" stroke="grey"></line> */ ;?>
        <?php /* </svg> */ ;?>

        <div id="container">
            <svg>

            </svg>
        </div>
    </div>

<?php \App\Section::stop(); ?>
<?=view('layer/main')->with(get_defined_vars())->render(); ?>