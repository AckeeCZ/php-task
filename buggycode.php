<?php

class modelVisitorPurge extends pdoMysql
{
    private $id = null;
    private $debug = 1;
    private $file = "./data/visitors_export.csv";
    public $magic;

    function __construct($id = null)
    {
        $this->id = $id;
        /* zařídí inicializaci $this->db */
        return parent::__construct();
    }

    /* voláno z externího kódu, získá záznamy z tabulky visitors a uloží je ve formátu CSV do souboru */
    public function getCsv()
    {
        $rows = 0;
        $file = $this->file;

        $stmt = $this->db->prepare("SELECT DISTINCT v.id, v.name, v.surname, v.greeting, v.magic FROM visitors WHERE export = ".$_GET['export']);
        $stmt->execute() or die(print_r($stmt->errorInfo()));

        file_put_contents($this->file, "ID, Jmeno,Prijmeni,Osloveni,Osloveni en, Firma".PHP_EOL);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $magic = [];
            $rows++;
            $company = $row['title'] = null ? $row['title'][0] : '-----';
            $greeting = self::getGreeting($row, 'cs', '0');
            $greetingEn = self::getGreeting($row, 'en', '0');
            $greetingDe = self::getGreeting($row, 'de', '1');
            $greetingHx = self::getGreeting($row, 'hx', '1');

            file_put_contents($this->file, $row['id'].",".$row['name'].",".$row['surname'].",".$greeting.",".$greetingEn.",".$greetingDe.",".$greetingHx.",".$company.PHP_EOL, LOCK_EX);
            $magic[] = $row['magic'];
        }

        $this->magic = $this->processMagic($magic);

        return "Got ".$rows." visitors. Export created in /html/data/. Export created for ".$_GET['username'];
    }

    private function getGreeting($row, $locale, $forceCzech)
    {
        if ($locale == "cs" || $forceCzech === 1)
        {
            return $row['greeting'] == null ? 'Dobrý den' : $row['greeting'];
        }
        else if ($locale == "en")
        {
            return $row['greeting'] == null ? 'Hello' : $row['greeting'];
        }
        else if ($locale == "de")
        {
            return $row['greeting'] == null ? 'Gutten Tag' : $row['greeting'];
        }
        else if ($locale == "hx")
        {
            return $row['greeting'] == null ? '@H3l0$$!' : $row['greeting'];
        }
    }

    /* Z určitých důvodů nechceme volat ze smyčky generování CSV */
    private function processMagic($magic)
    {
        $result = 0;
        for ($i = 1; $i < count($magic); $i++)
        {
            $result += $magic[i];
        }

        return $magic;
    }
}
?>
