<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use function simplehtmldom_1_5\file_get_html;
use Sunra\PhpSimple\HtmlDomParser;
use Illuminate\Console\Command;

/**
 * Class PersonalCrewScheduleReport
 * @package App\Console\Commands
 */
class PersonalCrewScheduleReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:crewReport {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This commands reads the html file and generates the crew report in json format';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->argument('date');

        /*
         * Check if date is a valid, if date is not a valid date then Carbon will throw and exception,
         * catch the exception and display the info in console along with the original exception message
         */
        if (null !== $date) {
            try {
                $date = Carbon::parse($date)->toDateString();
            } catch (\Exception $exception) {
                $this->info('Date is invalid, supported format is: Y-m-d');
                $this->error($exception->getMessage());
            }
        }

        $data = $this->readFile();
        if (null === $data) {
            $this->error('HTML file not found. Please contact to administrator');
        }

        
    }


    /**
     * This method reads the html file content and returns the transformed data
     *
     * @return |null
     */
    private function readFile()
    {
        $file = storage_path('app/assignment').'/roster_buster.html';


        if (!file_exists($file)) {
            return null;
        }


        $str=file_get_contents($file);
        $dom = new \DOMDocument();
        $dom->loadHTML($str);
        $tables = $dom->getElementsByTagName('table');
        $tableData = [];
        foreach($tables as $table) {
            if (!empty($table->nodeValue)) {
                $rows = $table->getElementsByTagName('tr');
                $tableRows = [];
                foreach ($rows as $row) {
                    if (!empty($row->nodeValue)) {
                        $cells = $row->getElementsByTagName('td');
                        $rowCells = [];
                        foreach ($cells as $cell) {
                            if (!empty($cell->nodeValue)) {
                                $rowCells[] = $cell->nodeValue;
                            }
                        }
                        $tableRows[] = $rowCells;
                    }
                }
                $tableData[] = $tableRows;
            }
        }
        return $tableData;
    }
}
