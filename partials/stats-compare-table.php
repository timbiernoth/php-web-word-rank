<?php
    $rank_data = $wr->api['wordrank']['rank'];
?>

<table style="float:left;" class="wordrank-compare-statistics-table">
    <thead>
        <tr>
            <th></th>
            <?php $i = 0; foreach ($rank_data as $url => $data) { ?>
                <th><a href="<?php echo $url; ?>" target="_blank">URL <?php echo $i; ?></a></th>
            <?php $i++; } ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'wordrank') {
                            echo '<td>';
                            for ($i = 0; $i < round($value/100); $i++) {
                                echo '<span></span>';
                            }
                            echo '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <td>WordRank</td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'wordrank') {
                            echo '<td>' . $value . '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <td>WordRank (ohne SPAM)</td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'wordrank_without_spam') {
                            echo '<td>' . $value . '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <td>SPAM-Score</td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'spam_score') {
                            echo '<td>' . $value . '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <td>SPAM-Signale</td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'spam_signals') {
                            echo '<td>' . $value['count'] . '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <td>Frequenz</td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'keywords_density_average') {
                            echo '<td>' . $value . '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <td>Wörter</td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'words') {
                            echo '<td>' . $value . '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <td>Wörter (einzigartig)</td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'words_unique') {
                            echo '<td>' . $value . '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
        <?php if (WORDTYPE == 'phrase' || WORDTYPE == 'combine') { ?>
            <tr>
                <td>Phrasen</td>
                <?php foreach ($rank_data as $url => $data) { ?>
                    <?php foreach ($data['statistics'] as $name => $value) { ?>
                        <?php
                            if ($name == 'phrases') {
                                echo '<td>' . $value . '</td>';
                            }
                        ?>
                    <?php } ?>
                <?php } ?>
            </tr>
            <tr>
                <td>Phrasen (einzigartig)</td>
                <?php foreach ($rank_data as $url => $data) { ?>
                    <?php foreach ($data['statistics'] as $name => $value) { ?>
                        <?php
                            if ($name == 'phrases_unqiue') {
                                echo '<td>' . $value . '</td>';
                            }
                        ?>
                    <?php } ?>
                <?php } ?>
            </tr>
            <tr>
                <td>Abschnitte</td>
                <?php foreach ($rank_data as $url => $data) { ?>
                    <?php foreach ($data['statistics'] as $name => $value) { ?>
                        <?php
                            if ($name == 'sentences') {
                                echo '<td>' . $value . '</td>';
                            }
                        ?>
                    <?php } ?>
                <?php } ?>
            </tr>
            <tr>
                <td>Abschnitte (einzigartig)</td>
                <?php foreach ($rank_data as $url => $data) { ?>
                    <?php foreach ($data['statistics'] as $name => $value) { ?>
                        <?php
                            if ($name == 'sentences_unqiue') {
                                echo '<td>' . $value . '</td>';
                            }
                        ?>
                    <?php } ?>
                <?php } ?>
            </tr>
        <?php } ?>
        <tr>
            <td>Keywords</td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'keywords_count') {
                            echo '<td>' . $value . '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <td>Übereinstimmung</td>
            <?php foreach ($rank_data as $url => $data) { ?>
                <?php foreach ($data['statistics'] as $name => $value) { ?>
                    <?php
                        if ($name == 'accordance') {
                            echo '<td>' . $value . '</td>';
                        }
                    ?>
                <?php } ?>
            <?php } ?>
        </tr>
    </tbody>
</table>
