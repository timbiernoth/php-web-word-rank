<?php
    $statistics = $wr->api['wordrank']['rank'][$url]['statistics'];
?>

<table style="float:right;" class="stats-table">
    <thead>
        <tr>
            <th>WordRank<br><?php echo $rank_type; ?></th>
            <th>WordRank<br><span class="white-space-nowrap">(ohne SPAM)</span></th>
            <th><span class="white-space-nowrap">SPAM-Score</span></th>
            <th>Wörter<br></th>
            <th>Wörter<br><span class="white-space-nowrap">(einzigartig)</span></th>
            <?php if (WORDTYPE == 'phrase' || WORDTYPE == 'combine') { ?>
                <th>Phrasen<br></th>
                <th>Phrasen<br><span class="white-space-nowrap">(einzigartig)</span></th>
                <th>Abschnitte<br></th>
                <th>Abschnitte<br><span class="white-space-nowrap">(einzigartig)</span></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-align-center"><?php echo $statistics['wordrank']; ?></td>
            <td class="text-align-center"><?php echo $statistics['wordrank_without_spam']; ?></td>
            <td class="text-align-center"><?php echo $statistics['spam_score']; ?></td>
            <td class="text-align-center"><?php echo $statistics['words']; ?></td>
            <td class="text-align-center"><?php echo $statistics['words_unique']; ?></td>
            <?php if (WORDTYPE == 'phrase' || WORDTYPE == 'combine') { ?>
                <td class="text-align-center"><?php echo $statistics['phrases']; ?></td>
                <td class="text-align-center"><?php echo $statistics['phrases_unique']; ?></td>
                <td class="text-align-center"><?php echo $statistics['sentences']; ?></td>
                <td class="text-align-center"><?php echo $statistics['sentences_unique']; ?></td>
            <?php } ?>
        <tr>
    </tbody>
</table>
