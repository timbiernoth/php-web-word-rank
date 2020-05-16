<?php
    $spam_signals = $wr->api['wordrank']['rank'][$url]['statistics']['spam_signals'];
    $count_max = 3;
    $title_attr = [
        'tt' => 'TT = Title-Tag // Verwenden Sie weniger Keywords im Title-Tag.',
        'sw' => 'SW = Summary WordRank // Reduzieren Sie die Priorität des Top-Wortes.',
        'kw' => 'KW = Keyword WordRank // Reduzieren Sie die Priorität des Wortes oder erhöhen Sie die Priorität der nachfolgenden Wörter.',
        'kd' => 'KD = Keyword Density // Reduzieren Sie die Anzahl des Wortes.',
    ];
?>

<table style="float:left;" class="quality-issues-results">
    <thead>
        <tr>
            <th>Priorität</th>
            <th>Anzahl (<?php echo $spam_signals['count']; ?>)</th>
            <th><span class="white-space-nowrap">SPAM-Signale</span></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Kritisch</td>
            <td><?php echo $spam_signals['priority']['critical']['count']; ?></td>
            <td><?php
                foreach ($spam_signals['priority']['critical']['type'] as $name => $count) {
                    if ($count >= 1 && $count <= $count_max) {
                        for ($i = 0; $i < $count; $i++) {
                            echo '<span class="quality-issue-critical" title="' . $title_attr[$name] . '">' . strtoupper($name) . '</span>' . '&nbsp;';
                        }
                    } else if ($count > $count_max) {
                        echo '<span class="quality-issue-critical" title="' . $title_attr[$name] . '">' . strtoupper($name) . ' +' . $count . '</span>' . '&nbsp;';
                    }
                }
            ?></td>
        </tr>
        <tr>
            <td>Hoch</td>
            <td><?php echo $spam_signals['priority']['high']['count']; ?></td>
            <td><?php
                foreach ($spam_signals['priority']['high']['type'] as $name => $count) {
                    if ($count >= 1 && $count <= $count_max) {
                        for ($i = 0; $i < $count; $i++) {
                            echo '<span class="quality-issue-high" title="' . $title_attr[$name] . '">' . strtoupper($name) . '</span>' . '&nbsp;';
                        }
                    } else if ($count > $count_max) {
                        echo '<span class="quality-issue-high" title="' . $title_attr[$name] . '">' . strtoupper($name) . ' +' . $count . '</span>' . '&nbsp;';
                    }
                }
            ?></td>
        </tr>
        <tr>
            <td>Mittel</td>
            <td><?php echo $spam_signals['priority']['medium']['count']; ?></td>
            <td><?php
                foreach ($spam_signals['priority']['medium']['type'] as $name => $count) {
                    if ($count >= 1 && $count <= $count_max) {
                        for ($i = 0; $i < $count; $i++) {
                            echo '<span class="quality-issue-medium" title="' . $title_attr[$name] . '">' . strtoupper($name) . '</span>' . '&nbsp;';
                        }
                    } else if ($count > $count_max) {
                        echo '<span class="quality-issue-medium" title="' . $title_attr[$name] . '">' . strtoupper($name) . ' +' . $count . '</span>' . '&nbsp;';
                    }
                }
            ?></td>
        </tr>
        <tr>
            <td>Niedrig</td>
            <td><?php echo $spam_signals['priority']['low']['count']; ?></td>
            <td><?php
                foreach ($spam_signals['priority']['low']['type'] as $name => $count) {
                    if ($count >= 1 && $count <= $count_max) {
                        for ($i = 0; $i < $count; $i++) {
                            echo '<span class="quality-issue-low" title="' . $title_attr[$name] . '">' . strtoupper($name) . '</span>' . '&nbsp;';
                        }
                    } else if ($count > $count_max) {
                        echo '<span class="quality-issue-low" title="' . $title_attr[$name] . '">' . strtoupper($name) . ' +' . $count . '</span>' . '&nbsp;';
                    }
                }
            ?></td>
        </tr>
    </tbody>
</table>
