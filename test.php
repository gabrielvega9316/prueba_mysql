<?php

class UserStats {
    private $dateFrom;
    private $dateTo;
    private $totalClicks;

    public function __construct($dateFrom, $dateTo, $totalClicks = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->totalClicks = $totalClicks;
    }

    public function getDateFrom(){
        return $this->dateFrom;
    }

    public function setDateFrom($dateFrom){
        $this->dateFrom = $dateFrom;
    }

    public function getDateTo(){
        return $this->dateTo;
    }

    public function setDateTo($dateTo){
        $this->dateTo = $dateTo;
    }

    public function getTotalClicks(){
        return $this->totalClicks;
    }

    public function setTotalClicks($totalClicks){
        $this->totalClicks = $totalClicks;
    }

    public function getStats(){
        require_once 'config.php';

        $dateFrom = $this->getDateFrom();
        $dateTo = $this->getDateTo();
        $totalClicks = $this->getTotalClicks();

        $conn = new mysqli($servename, $username, $password, $database);
        if ($conn->connect_errno) {
            echo "Fallo al conectar a MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error;
        }
        echo $conn->host_info . "\n";

        $sql = "SELECT CONCAT(u.first_name, ' ', u.last_name) AS fullname, SUM(us.views) AS total_views, SUM(clicks) AS total_clicks, SUM(us.conversions) AS total_conversions, 
        ROUND((SUM(us.conversions)/ SUM(us.clicks))*100, 2) AS cr, MAX(us.date) AS last_date FROM `user_stats` AS us
        INNER JOIN users AS u ON us.user_id = u.id
        WHERE us.date >= '$dateFrom' AND us.date <= '$dateTo' AND u.status = 'active'
        GROUP BY us.user_id";
        
        if(!is_null($totalClicks)){
            $sql .= " HAVING total_clicks >= '$totalClicks';";
        }

        $result = $conn->query($sql);

        $output = array();

        while($row = $result->fetch_assoc()){
            $data = array(
                'full_name' => $row['fullname'],
                'total_views' => $row['total_views'],
                'total_clicks' => $row['total_clicks'],
                'total_conversions' => $row['total_conversions'],
                'cr' => $row['cr'],
                'last_date' => date("Y-m-d", strtotime($row['last_date'])),
            );

            $output[] = $data;
        }

        print_r($output);

        $conn->close();
    }
}

$userStats = new UserStats('2022-10-01', '2022-10-16', 9000);
$stats = $userStats->getStats();
echo $stats;

?>