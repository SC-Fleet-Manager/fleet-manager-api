<?php

namespace App\Controller\BackOffice\Monitoring;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OPCacheMonitoringController extends AbstractController
{
    /**
     * @see https://haydenjames.io/php-performance-opcache-control-panels/
     * @see https://gist.github.com/ck-on/4959032
     */
    #[Route("/bo/monitoring/opcache", name: "bo_monitoring_opcache", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
        $time = time();

        if ($request->query->getBoolean('reset', false)) {
            opcache_reset();

            return $this->redirectToRoute('bo_monitoring_opcache');
        }
        if ($request->query->getBoolean('recheck', false)) {
            $files = opcache_get_status();
            if (!empty($files['scripts'])) {
                foreach ($files['scripts'] as $file => $value) {
                    opcache_invalidate($file);
                }
            }

            return $this->redirectToRoute('bo_monitoring_opcache');
        }

        $html = '';

        $configuration = opcache_get_configuration();

        $host = $request->getHost();
        $version = ['Host' => $host];
        $version['PHP Version'] = 'PHP '.(defined('PHP_VERSION') ? PHP_VERSION : '???').' '.(defined('PHP_SAPI') ? PHP_SAPI : '').' '.(defined('PHP_OS') ? ' '.PHP_OS : '');
        $version['OPCache Version'] = empty($configuration['version']['version']) ? '???' : $configuration['version']['opcache_product_name'].' '.$configuration['version']['version'];
        $this->printTable($version);

        $status = opcache_get_status();

        $uptime = [];
        if (!empty($status['opcache_'.'statistics']['start_time'])) {
            $uptime['uptime'] = $this->timeSince($time, $status['opcache_'.'statistics']['start_time'], 1, '');
        }
        if (!empty($status['opcache_'.'statistics']['last_restart_time'])) {
            $uptime['last_restart'] = $this->timeSince($time, $status['opcache_'.'statistics']['last_restart_time']);
        }
        if (!empty($uptime)) {
            $this->printTable($uptime);
        }

        if (!empty($status['cache_full'])) {
            $status['memory_usage']['cache_full'] = $status['cache_full'];
        }

        $html .= $this->graphsDisplay();
        $html .= '<h2 id="memory">memory</h2>';
        $html .= $this->printTable($status['memory_usage']);
        unset($status['opcache_'.'statistics']['start_time'], $status['opcache_'.'statistics']['last_restart_time']);
        $html .= '<h2 id="statistics">statistics</h2>';
        $html .= $this->printTable($status['opcache_'.'statistics']);

        if (!empty($configuration['blacklist'])) {
            $html .= '<h2 id="blacklist">blacklist</h2>';
            $html .= $this->printTable($configuration['blacklist']);
        }

        if (isset($configuration['directives']['opcache.optimization_level'])) {
            $html .= '<h2 id="optimization">optimization levels</h2>';
            $html .= '<p>'.sprintf('%x', $configuration['directives']['opcache.optimization_level']).'</p>';
            $levelset = strrev(base_convert($configuration['directives']['opcache.optimization_level'], 10, 2));
            $levels = [
                1 => '<a href="http://wikipedia.org/wiki/Common_subexpression_elimination">Constants subexpressions elimination</a> (CSE) true, false, null, etc.<br />Optimize series of ADD_STRING / ADD_CHAR<br />Convert CAST(IS_BOOL,x) into BOOL(x)<br />Convert <a href="http://www.php.net/manual/internals2.opcodes.init-fcall-by-name.php">INIT_FCALL_BY_NAME</a> + <a href="http://www.php.net/manual/internals2.opcodes.do-fcall-by-name.php">DO_FCALL_BY_NAME</a> into <a href="http://www.php.net/manual/internals2.opcodes.do-fcall.php">DO_FCALL</a>',
                2 => 'Convert constant operands to expected types<br />Convert conditional <a href="http://php.net/manual/internals2.opcodes.jmp.php">JMP</a>  with constant operands<br />Optimize static <a href="http://php.net/manual/internals2.opcodes.brk.php">BRK</a> and <a href="<a href="http://php.net/manual/internals2.opcodes.cont.php">CONT</a>',
                3 => 'Convert $a = $a + expr into $a += expr<br />Convert $a++ into ++$a<br />Optimize series of <a href="http://php.net/manual/internals2.opcodes.jmp.php">JMP</a>',
                4 => 'PRINT and ECHO optimization (<a href="https://github.com/zend-dev/ZendOptimizerPlus/issues/73">defunct</a>)',
                5 => 'Block Optimization - most expensive pass<br />Performs many different optimization patterns based on <a href="http://wikipedia.org/wiki/Control_flow_graph">control flow graph</a> (CFG)',
                9 => 'Optimize <a href="http://wikipedia.org/wiki/Register_allocation">register allocation</a> (allows re-usage of temporary variables)',
                10 => 'Remove NOPs',
            ];
            $html .= '<table width="600" border="0" cellpadding="3"><tbody><tr class="h"><th>Pass</th><th>Description</th></tr>';
            foreach ($levels as $pass => $description) {
                $disabled = substr($levelset, $pass - 1, 1) !== '1' || $pass == 4 ? ' white' : '';
                $html .= '<tr><td class="v center middle'.$disabled.'">'.$pass.'</td><td class="v'.$disabled.'">'.$description.'</td></tr>';
            }
            $html .= '</table>';
        }

        if ($request->query->getBoolean('dump', false)) {
            foreach ($configuration as $key => $value) {
                $html .= '<h2>'.$key.'</h2>';
                $html .= $this->printTable($configuration[$key]);
            }
        }

        if ($request->query->getBoolean('files', false)) {
            $html .= '<h2>Cached scripts</h2>';
            $files = opcache_get_status()['scripts'];
            $sorting = $request->query->get('sort', 'memory');
            usort($files, static function ($f1, $f2) use ($sorting): int {
                switch ($sorting) {
                    case 'hits':
                        return $f2['hits'] - $f1['hits'];
                    case 'memory':
                        return $f2['memory_consumption'] - $f1['memory_consumption'];
                    case 'last_used':
                        return $f2['last_used_timestamp'] - $f1['last_used_timestamp'];
                }

                return 0;
            });
            $files = array_slice($files, 0, 150);

            $html .= '<table><thead><tr><th>Path</th><th>Hits</th><th>Memory</th><th>Last used since (sec.)</th></tr></thead><tbody>';
            foreach ($files as $file) {
                $since = time() - $file['last_used_timestamp'];
                $memory = number_format((int) $file['memory_consumption'] / 1024, 1);
                $html .= <<<EOT
                        <tr>
                            <td>{$file['full_path']}</td>
                            <td>{$file['hits']}</td>
                            <td>{$memory} K</td>
                            <td>{$since}</td>
                        </tr>
                    EOT;
            }
            $html .= '</tbody></table>';
        }

        return $this->render('back_office/monitoring/opcache.html.twig', [
            'content' => $html,
        ]);
    }

    private function timeSince($time, $original, $extended = 0, $text = 'ago'): string
    {
        $time -= $original;
        $day = $extended ? floor($time / 86400) : round($time / 86400, 0);
        if ($time < 86400) {
            if ($time < 60) {
                $amount = $time;
                $unit = 'second';
            } elseif ($time < 3600) {
                $amount = floor($time / 60);
                $unit = 'minute';
            } else {
                $amount = floor($time / 3600);
                $unit = 'hour';
            }
        } elseif ($day < 14) {
            $amount = $day;
            $unit = 'day';
        } elseif ($day < 56) {
            $amount = floor($day / 7);
            $unit = 'week';
        } elseif ($day < 672) {
            $amount = floor($day / 30);
            $unit = 'month';
        } else {
            $amount = (int) (2 * ($day / 365)) / 2;
            $unit = 'year';
        }

        if ($amount !== 1) {
            $unit .= 's';
        }
        if ($extended && $time > 60) {
            $text = ' and '.$this->timeSince($time, $time < 86400 ? ($time < 3600 ? $amount * 60 : $amount * 3600) : $day * 86400, 0, '').$text;
        }

        return $amount.' '.$unit.' '.$text;
    }

    private function printTable($array, $headers = false): string
    {
        $html = '';
        if (empty($array) || !is_array($array)) {
            return $html;
        }

        $html .= '<table border="0" cellpadding="3" width="600">';
        if (!empty($headers)) {
            if (!is_array($headers)) {
                $headers = array_keys(reset($array));
            }
            $html .= '<tr class="h">';
            foreach ($headers as $value) {
                $html .= '<th>'.$value.'</th>';
            }
            $html .= '</tr>';
        }
        foreach ($array as $key => $value) {
            $html .= '<tr>';
            if (!is_numeric($key)) {
                $key = ucwords(str_replace('_', ' ', $key));
                $html .= '<td class="e">'.$key.'</td>';
                if (is_numeric($value)) {
                    if ($value > 1048576) {
                        $value = round($value / 1048576, 1).'M';
                    } elseif (is_float($value)) {
                        $value = round($value, 1);
                    }
                }
            }
            if (is_array($value)) {
                foreach ($value as $column) {
                    $html .= '<td class="v">'.$column.'</td>';
                }
                $html .= '</tr>';
            } else {
                $html .= '<td class="v">'.$value.'</td></tr>';
            }
        }
        $html .= '</table>';

        return $html;
    }

    private function graphsDisplay(): string
    {
        $graphs = [];
        $colors = ['green', 'brown', 'red'];
        $primes = [223, 463, 983, 1979, 3907, 7963, 16229, 32531, 65407, 130987];
        $configuration = opcache_get_configuration();
        $status = opcache_get_status();

        $graphs['memory']['total'] = $configuration['directives']['opcache.memory_consumption'];
        $graphs['memory']['free'] = $status['memory_usage']['free_memory'];
        $graphs['memory']['used'] = $status['memory_usage']['used_memory'];
        $graphs['memory']['wasted'] = $status['memory_usage']['wasted_memory'];

        $graphs['keys']['total'] = $status['opcache_'.'statistics']['max_cached_keys'];
        foreach ($primes as $prime) {
            if ($prime >= $graphs['keys']['total']) {
                $graphs['keys']['total'] = $prime;
                break;
            }
        }
        $graphs['keys']['free'] = $graphs['keys']['total'] - $status['opcache_'.'statistics']['num_cached_keys'];
        $graphs['keys']['scripts'] = $status['opcache_'.'statistics']['num_cached_scripts'];
        $graphs['keys']['wasted'] = $status['opcache_'.'statistics']['num_cached_keys'] - $status['opcache_'.'statistics']['num_cached_scripts'];

        $graphs['hits']['total'] = 0;
        $graphs['hits']['hits'] = $status['opcache_'.'statistics']['hits'];
        $graphs['hits']['misses'] = $status['opcache_'.'statistics']['misses'];
        $graphs['hits']['blacklist'] = $status['opcache_'.'statistics']['blacklist_misses'];
        $graphs['hits']['total'] = array_sum($graphs['hits']);

        $graphs['restarts']['total'] = 0;
        $graphs['restarts']['manual'] = $status['opcache_'.'statistics']['manual_restarts'];
        $graphs['restarts']['keys'] = $status['opcache_'.'statistics']['hash_restarts'];
        $graphs['restarts']['memory'] = $status['opcache_'.'statistics']['oom_restarts'];
        $graphs['restarts']['total'] = array_sum($graphs['restarts']);

        $html = '';
        foreach ($graphs as $caption => $graph) {
            $html .= '<div class="graph"><div class="h">'.$caption.'</div><table border="0" cellpadding="0" cellspacing="0">';
            foreach ($graph as $label => $value) {
                if ($label == 'total') {
                    $key = 0;
                    $total = $value;
                    $totaldisplay = '<td rowspan="3" class="total"><span>'.($total > 999999 ? round($total / 1024 / 1024).'M' : ($total > 9999 ? round($total / 1024).'K' : $total)).'</span><div></div></td>';
                    continue;
                }
                $percent = $total ? floor($value * 100 / $total) : '';
                $percent = !$percent || $percent > 99 ? '' : $percent.'%';
                $html .= '<tr>'.$totaldisplay.'<td class="actual">'.($value > 999999 ? round($value / 1024 / 1024).'M' : ($value > 9999 ? round($value / 1024).'K' : $value)).'</td><td class="bar '.$colors[$key].'" height="'.$percent.'">'.$percent.'</td><td>'.$label.'</td></tr>';
                ++$key;
                $totaldisplay = '';
            }
            $html .= '</table></div>'."\n";
        }

        return $html;
    }
}
