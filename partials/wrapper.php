<!doctype html><html>
    <head>
        <title>WordRank</title>
        <meta charset="utf-8">

        <?php require_once 'style-fonts.php'; ?>
        <?php require_once 'style.php'; ?>

    </head>
    <body id="top" class="dark">

        <div class="sidebar">

            <h1 class="text-align-center"><a href="/dev/">WordRank</a></h1>

            <hr>

            <div style="float:left;width:100%;">
                <?php require_once 'wordrank-form.php'; ?>
            </div>

            <hr>

            <?php if ($get_url->error !== true) { ?>

                <div style="float:left;width:100%;padding:0 10px;">

                    <?php if ($get_url->compare !== false) { ?>

                        <ul>
                            <li><strong>Vergleich</strong></li>
                            <li>
                                <ul>
                                    <li><a href="#keyword-data">Statistiken</a></li>
                                    <li><a href="#keyword-recommendations">Empfehlungen</a></li>
                                </ul>
                            </li>
                        </ul>

                    <?php } ?>

                    <ul>
                        <li><strong>Einzelseiten</strong></li>
                        <li>
                            <ul>
                                <?php foreach ($wr->api['wordrank']['urls'] as $key => $url) { ?>

                                    <li><a href="#url-<?php echo $key; ?>" title="<?php echo $url; ?>">URL <?php echo $key; ?></a></li>

                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                </div>

                <?php if (DEBUG !== false) { $load_time->setStop(); ?>

                    <hr>

                    <div style="float:left;width:100%;padding:0 10px;color: #666;">
                        <strong>Ladezeit:</strong> <?php echo round($load_time->stop, 2); ?> Sek.
                    </div>

                <?php } ?>

            <?php } ?>

        </div>

        <div class="content">

            <?php if ($get_url->error !== true) { ?>

                <?php if ($get_url->compare !== false) { ?>

                    <div style="float:left;width:100%;">

                        <div style="float:left;width:100%;">

                            <h2 id="url-compare" style="margin-top:10px;">Vergleich der Seiten</h2>

                            <hr>

                        </div>

                        <div style="float:left;width:100%;">

                            <h3 id="keyword-data">Keyword Statistiken von allen Seiten</h3>

                            <?php require_once 'stats-compare-table.php'; ?>

                            <hr>

                        </div>

                        <div style="float:left;width:100%;">

                            <h3 id="keyword-recommendations">Keyword Empfehlungen von allen Seiten</h3>

                            <?php require_once 'wordrank-compare-table.php'; ?>

                            <hr>

                        </div>

                    </div>

                <?php } ?>

                <div style="float:left;width:100%;">
                    <h2 id="keyword-issues">Keywords und SPAM-Signale auf der Einzelseite</h2>
                </div>

                <hr>

                <?php foreach ($wr->api['wordrank']['urls'] as $key => $url) { ?>

                    <div style="float:left;width:100%;">

                        <?php if ($key >= 1) { ?>
                            <hr>
                        <?php } ?>

                        <h3 id="url-<?php echo $key; ?>"><a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a></h3>

                        <hr>

                        <div style="float:left;width:100%;">

                            <?php include 'quality-issues-results-table.php'; ?>

                            <?php include 'stats-table.php'; ?>

                        </div>

                        <hr>

                        <?php include 'wordrank-table.php'; ?>

                    </div>

                <?php } ?>

                <div style="float:left;width:100%;">

                    <hr>

                    <br>

                    <a href="#top">Zurück nach oben</a>

                    <hr>

                </div>

            <?php } else {echo $get_url->errorMassage;} ?>

        </div>

        <div class="javascripts">
            <script src="https://www.wordrank.org/dev/assets/js/jquery-3.3.1.min.js"></script>
            <script>
                (function($) {

                    $('.wordrank-table .to-toggle').on('click ontouchstart', function() {
                        var $this = $(this),
                            $wrapper = $this.closest('td'),
                            $toggle = $wrapper.find('.toggle');

                        $toggle.toggleClass('hide');
                    });

                })(jQuery);
            </script>
        </div>

    </body>
</html>
