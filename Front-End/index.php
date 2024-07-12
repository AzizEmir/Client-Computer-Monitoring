<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HİBİS Monitoring ~ HOME</title>
    <link rel="icon" type="image/x-icon" href="./marka-icon2.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.8.1/cdn.min.js" defer></script>
    <style>
        .chart-container {
            display: inline-block;
            margin-right: 20px;
            text-align: center;
            vertical-align: top;
        }
        .chart-pair {
            display: inline-block;
            margin-right: 50px;
            margin-bottom: 50px;
        }
        .table-container {
            max-height: 320px;
            overflow-y: auto;
        }
        .table-container thead {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 1;
        }
    </style>
</head>
<body>
<div class="flex flex-col items-center justify-center mt-32" x-cloak x-data="appData()" x-init="appInit()">
    <div class="flex flex-col">
        <div class="fixed inset-x-0 top-0 z-50 h-0.5 mt-0.5 bg-blue-500" :style="`width: ${percent}%`"></div>
        <nav class="flex justify-around py-4 bg-white/80 backdrop-blur-md shadow-md w-full fixed top-0 left-0 right-0 z-10" style="background: linear-gradient(90deg, rgba(0,0,0,0.2841737378545168) 14%,  rgba(205,236,26,0.5816527294511555) 95%);">
            <div class="flex items-center">
                <a class="cursor-pointer">
                    <h3 class="text-2xl font-medium text-blue-500">
                        <img class="h-12 object-cover" src="./marka-icon2.png" alt="Store Logo">
                    </h3>
                </a>
            </div>
            <div class="items-center hidden space-x-8 lg:flex">
                <a class="flex text-gray-600 hover:text-blue-500 cursor-pointer transition-colors duration-300">Home</a>
                <a class="flex text-gray-600 hover:text-blue-500 cursor-pointer transition-colors duration-300">Services</a>
                <a class="flex text-gray-600 hover:text-blue-500 cursor-pointer transition-colors duration-300">Pricing</a>
                <a class="flex text-gray-600 hover:text-blue-500 cursor-pointer transition-colors duration-300">About Us</a>
            </div>
        </nav>
    </div>
</div>

<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:9827/cpuusage");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $uniqueComputerNames = array_keys($data);
        foreach ($uniqueComputerNames as $computerName):
            $computerNameSafe = htmlspecialchars($computerName);
            $sessionInfo = $data[$computerName]['oturum_bilgisi'];
            $rowIndex = 1;
            ?>
            <div class="chart-pair">
                <div class="chart-container">
                    <canvas id="pie-chart-<?= $computerNameSafe ?>" width="400" height="400"></canvas>
                    <div>Bilgisayar Adı: <?= $computerNameSafe ?></div>
                </div>
                <div class="chart-container">
                    <canvas id="bar-chart-grouped-<?= $computerNameSafe ?>" width="400" height="400"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="polar-chart-<?= $computerNameSafe ?>" width="400" height="400"></canvas>
                </div>
                <div class="chart-container">
                    <div class="flex flex-col">
                        <div class="overflow-x-auto sm:mx-0.5 lg:mx-0.5">
                            <div class="py-2 inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="overflow-hidden table-container">
                                    <table class="min-w-full">
                                        <thead class="bg-white border-b">
                                        <tr>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">#</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">User</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">Date</th>
                                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($sessionInfo as $session):
                                            $user = htmlspecialchars($session['user']);
                                            $date = htmlspecialchars($session['date']);
                                            $status = htmlspecialchars($session['status']);
                                            $tableRowClass = ($rowIndex % 2 === 0) ? 'bg-white border-b' : 'bg-gray-100 border-b';
                                            ?>
                                            <tr class="<?= $tableRowClass ?>">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $rowIndex ?></td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap"><?= $user ?></td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap"><?= $date ?></td>
                                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap"><?= $status ?></td>
                                            </tr>
                                            <?php $rowIndex++; endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
    } else {
        echo 'JSON decode error: ' . json_last_error_msg();
    }
}
curl_close($ch);
?>

<script>
    <?php if (isset($data) && is_array($data)): ?>
    <?php foreach ($uniqueComputerNames as $computerName):
        $computerNameSafe = htmlspecialchars($computerName);
        $cpuUsage = isset($data[$computerName]['cpu_yuzde']) ? intval(str_replace(',', '.', $data[$computerName]['cpu_yuzde'])) : 0;
        $freeUsage = 100 - $cpuUsage;
        $ramUsage = isset($data[$computerName]['ram_yuzde']) ? floatval(str_replace(',', '.', $data[$computerName]['ram_yuzde'])) : 0;
        $ramCapacity = isset($data[$computerName]['ram_kapasite']) ? floatval(str_replace(',', '.', $data[$computerName]['ram_kapasite'])) : 0;
        $diskUsage = isset($data[$computerName]['disk_kullanim']) ? floatval(str_replace(',', '.', $data[$computerName]['disk_kullanim'])) : 0;
        $diskCapacity = isset($data[$computerName]['disk_kapasite']) ? floatval(str_replace(',', '.', $data[$computerName]['disk_kapasite'])) : 0;
        ?>
        var ctxPie<?= str_replace(' ', '_', $computerNameSafe) ?> = document.getElementById("pie-chart-<?= $computerNameSafe ?>").getContext("2d");
        var chartPie<?= str_replace(' ', '_', $computerNameSafe) ?> = new Chart(ctxPie<?= str_replace(' ', '_', $computerNameSafe) ?>, {
            type: "pie",
            data: {
                labels: ["CPU Dolu", "CPU Bos"],
                datasets: [{
                    label: "CPU Kullanimi",
                    backgroundColor: ["#2196F3", "#FF5722"],
                    data: [<?= $cpuUsage ?>, <?= $freeUsage ?>]
                }]
            },
            options: {
                title: {
                    display: true,
                    text: "CPU (İşlemci) kullanımı Yüzdelik <?= $computerNameSafe ?>"
                }
            }
        });

        var ctxBarGrouped<?= str_replace(' ', '_', $computerNameSafe) ?> = document.getElementById("bar-chart-grouped-<?= $computerNameSafe ?>").getContext("2d");
        var chartBarGrouped<?= str_replace(' ', '_', $computerNameSafe) ?> = new Chart(ctxBarGrouped<?= str_replace(' ', '_', $computerNameSafe) ?>, {
            type: "bar",
            data: {
                labels: ["RAM Kullanımı | MiB"],
                datasets: [
                    {
                        label: "RAM Kullanımı",
                        backgroundColor: "#4CAF50",
                        data: [<?= $ramUsage ?>]
                    },
                    {
                        label: "RAM Kapasitesi",
                        backgroundColor: "#FFC107",
                        data: [<?= $ramCapacity ?>]
                    }
                ]
            },
            options: {
                title: {
                    display: true,
                    text: "RAM Kullanımı <?= $computerNameSafe ?>"
                }
            }
        });

        var ctxPolar<?= str_replace(' ', '_', $computerNameSafe) ?> = document.getElementById("polar-chart-<?= $computerNameSafe ?>").getContext("2d");
        var chartPolar<?= str_replace(' ', '_', $computerNameSafe) ?> = new Chart(ctxPolar<?= str_replace(' ', '_', $computerNameSafe) ?>, {
            type: "polarArea",
            data: {
                labels: ["Disk Kullanımı", "Disk Kapasitesi"],
                datasets: [{
                    label: "Disk Kullanımı",
                    backgroundColor: ["#FF6384", "#36A2EB"],
                    data: [<?= $diskUsage ?>, <?= $diskCapacity ?>]
                }]
            },
            options: {
                title: {
                    display: true,
                    text: "Disk Kullanımı <?= $computerNameSafe ?>"
                }
            }
        });
    <?php endforeach; ?>
    <?php endif; ?>
</script>
</body>
</html>
