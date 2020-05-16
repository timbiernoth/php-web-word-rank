<?php
    $rank_compare = $wr->api['wordrank']['rank_compare'];
?>

<table class="wordrank-compare-table">
    <thead>
        <tr>
            <th>Nr.<br></th>
            <th>Keywords<br>(<?php echo count($rank_compare['keywords']); ?>)</th>
            <th class="text-align-center">Wörter<br></th>
            <th class="text-align-center">WordRank<br><?php echo $rank_type; ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>

        <?php
            $c = 1;
            foreach ($rank_compare['keywords'] as $keyword => $data) {

                ?>
                    <tr>
                        <td class="text-align-center"><?php echo $c; ?></td>
                        <td><?php
                            $marked_phrase = $keyword;
                            $origin_phrase = $keyword;

                            if ( $data['recommendations']['match'] === false &&
                                $data['recommendations']['mismatch'] !== false) {
                                $marked_phrase = '<span class="quality-issue-low">' . $origin_phrase . '</span>';
                            }

                            if ( $data['recommendations']['match'] === false &&
                                $data['recommendations']['mismatch_important'] !== false) {
                                $marked_phrase = '<span class="quality-issue-high">' . $origin_phrase . '</span>';
                            }

                            if ( $data['recommendations']['match'] ) {
                                $marked_phrase = '<span class="quality-issue-good">' . $origin_phrase . '</span>';
                            }

                            echo $marked_phrase;
                        ?></td>
                        <td class="text-align-center"><?php echo $data['words_count']; ?></td>
                        <td class="text-align-center"><?php echo round($data['wordrank'], 2); ?></td>
                        <td>
                            <?php
                                $value = round($data['wordrank']);
                                for ($i = 0; $i < $value; $i++) {
                                    echo '<span></span>';
                                }
                            ?>
                        </td>
                    </tr>
                <?php
                $c++;
            }
        ?>

    </tbody>
</table>
