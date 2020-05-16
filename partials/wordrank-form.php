<?php
    $urls = $get_url->urls;
    $query = $get_url->query;

    if (RANKTYPE !== 'all') {
        $rank_type = ' (' . RANKTYPE . ')';
    } else {
        $rank_type = '';
    }
?>

<form class="form-wordrank" action="/dev/">

    <!--
    <div class="form-wordrank-input">
        <input class="input-url" name="query" value="<?php if (isset($query) && $query !== '') {echo $query;} ?>" placeholder="Keyword (optional)">
    </div>

    <hr>
    -->

    <div class="form-wordrank-input">
        <input class="input-url" name="url[]" value="<?php if (isset($urls[0]) && $urls[0] !== '') {echo $urls[0] ;} else {echo 'https://';} ?>" placeholder="https://">
    </div>

    <hr>

    <div class="form-wordrank-input">
        <input class="input-url" name="url[]" value="<?php if (isset($urls[1]) && $urls[1] !== '') {echo $urls[1] ;} else {echo 'https://';} ?>" placeholder="https://">
    </div>

    <div class="form-wordrank-input">
        <input class="input-url" name="url[]" value="<?php if (isset($urls[2]) && $urls[2] !== '') {echo $urls[2] ;} else {echo 'https://';} ?>" placeholder="https://">
    </div>

    <div class="form-wordrank-input">
        <input class="input-url" name="url[]" value="<?php if (isset($urls[3]) && $urls[3] !== '') {echo $urls[3] ;} else {echo 'https://';} ?>" placeholder="https://">
    </div>

    <div class="form-wordrank-input">
        <input class="input-url" name="url[]" value="<?php if (isset($urls[4]) && $urls[4] !== '') {echo $urls[4] ;} else {echo 'https://';} ?>" placeholder="https://">
    </div>

    <div class="form-wordrank-input text-align-left">
        <input id="wordrank_compare" name="compare" type="checkbox"<?php if (isset($_GET['compare']) && $_GET['compare'] == 'on') {echo ' checked' ;} ?>><label for="wordrank_compare"> Vergleichen</label>
    </div>

    <hr>

    <?php if (SHOW_WORDTYPE_SELECT !== false) { ?>

        <div class="form-wordrank-widget">

            <label for="wordrank_rank">Rank-Typ</label>
            <select id="wordrank_rank" name="rank">
                <option value="all" <?php if (RANKTYPE == 'all') {echo 'selected' ;} ?>>Komplett</option>
                <option disabled>------------</option>
                <option value="tag" <?php if (RANKTYPE == 'tag') {echo 'selected' ;} ?>>HTML-Tags</option>
                <option value="position" <?php if (RANKTYPE == 'position') {echo 'selected' ;} ?>>Positionen</option>
                <option value="mention" <?php if (RANKTYPE == 'mention') {echo 'selected' ;} ?>>Erwähnungen</option>
                <option value="repeat" <?php if (RANKTYPE == 'repeat') {echo 'selected' ;} ?>>Wiederholungen</option>
            </select>

        </div>

        <div class="form-wordrank-widget">

            <label for="wordrank_word">Word-Typ</label>
            <select id="wordrank_word" name="word">

                <?php if (ALLOW_WORDTYPE_COMBINE == true) { ?>
                    <option value="combine" <?php if (WORDTYPE == 'combine') {echo 'selected' ;} ?>>Kombiniert</option>
                    <option disabled>--------------</option>
                <?php } ?>

                <?php if (ALLOW_WORDTYPE_WORD == true) { ?>
                    <option value="word" <?php if (WORDTYPE == 'word') {echo 'selected' ;} ?>>Einzelwort</option>
                <?php } ?>

                <?php if (ALLOW_WORDTYPE_PHRASE == true) { ?>
                    <option value="phrase" <?php if (WORDTYPE == 'phrase') {echo 'selected' ;} ?>>Wortphrase</option>
                <?php } ?>

            </select>

        </div>

        <hr>

    <?php } ?>

    <button type="submit">Go!</button>

</form>
