<?php
    $rank_url = $wr->api['wordrank']['rank'][$url];
    $title_attr = [
        'tt' => 'TT = Title-Tag // Verwenden Sie weniger Keywords im Title-Tag.',
        'ht' => 'HT = Headline-Tag // Verwenden Sie weniger Keywords in den H1-H6 Tags.',
        'et' => 'ET = Emphasing-Tag // Verwenden Sie weniger Keywords in den strong/b und em/i Tags.',
        'sw' => 'SW = Summary WordRank // Reduzieren Sie die Priorität des Top-Wortes.',
        'kw' => 'KW = Keyword WordRank // Reduzieren Sie die Priorität des Wortes oder erhöhen Sie die Priorität der nachfolgenden Wörter.',
        'kd' => 'KD = Keyword Density // Reduzieren Sie die Anzahl des Wortes.',
    ];
?>

<table class="wordrank-table">
    <thead>
        <tr>
            <th>Nr.<br></th>
            <th>Keywords<br>(<?php echo $rank_url['statistics']['keywords_count']; ?>)</th>
            <th class="text-align-center">Wörter<br></th>
            <th><span class="white-space-nowrap">SPAM-Signale</span></th>
            <th class="text-align-center">Frequenz<br><span class="white-space-nowrap">(∅ <?php echo $rank_url['statistics']['keywords_density_average']; ?>)</span></th>
            <th class="text-align-center">WordRank<br><?php echo $rank_type; ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>

        <?php
            $c = 1;
            foreach ($rank_url['keywords'] as $keyword => $data) {
        ?>

            <tr>
                <td class="text-align-center"><?php echo $c; ?></td>
                <td><?php

                    echo $keyword;

                    if ( ! empty($data['similar_keywords']) ) {
                        echo '&nbsp;<span class="to-toggle">+</span><div class="toggle hide"><ul>';

                        foreach ($data['similar_keywords'] as $keyword_similar => $wordrank) {
                            echo '<li>' . $keyword_similar . '</li>';
                        }

                        echo '</ul></div>';
                    }

                ?></td>
                <td class="text-align-center"><?php echo $data['words_count']; ?></td>
                <td><?php

                    foreach ($data['spam_signals']['priority'] as $priority => $datas) {
                        foreach ($datas['type'] as $name => $is) {
                            if ($is !== false) {
                                echo '<span class="quality-issue-' .
                                    $priority .
                                    '" title="' . $title_attr[$name] . '">' . strtoupper($name) . '</span>&nbsp;';
                            }
                        }
                    }

                ?></td>
                <td class="text-align-center"><?php echo $data['density']; ?></td>
                <td class="text-align-center"><?php echo $data['wordrank']; ?></td>
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
