<?php

namespace RedSquirrelStudio\LaravelBackpackExportOperation\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class CrudExportFromGenerator implements FromGenerator, ShouldAutoSize
{
    protected $export_log;
    protected $crud;

    /**
     * @param int $export_log_id
     */
    public function __construct(int $export_log_id)
    {
        $log_model = config('backpack.operations.export.export_log_model');
        $this->export_log =  $log_model::find($export_log_id);
        $this->crud = app('crud');
    }

    /**
     * Generator for excel export
     * @return \Generator
     */
    public function generator(): \Generator
    {
        $log_model = config('backpack.operations.export.export_log_model');
        $log = $log_model::find($this->export_log->id);

        CRUD::setModel($log->model);

        $entries = $log->model::all();

        $config = $log->config;

        //Yield row first
        yield(array_column($log->config,'label'));

        foreach ($entries as $rowValue) {
            $tempRow = null;
            foreach($config as $column){
                $tempRow[] .= $rowValue[$column['name']];
            }
            yield $tempRow;
        }
    }

    /**
     * @return View
     */
    public function view(): View
    {
        $log_model = config('backpack.operations.export.export_log_model');
        $log = $log_model::find($this->export_log->id);

        CRUD::setModel($log->model);

        $entries = $log->model::all();
        return view('export-operation::exports.crud-export', [
            'config' => $log->config,
            'entries' => $entries,
            'crud' => $this->crud
        ]);
    }

    /**
     * @return Model
     */
    protected function getExportLog(): Model
    {
        return $this->export_log;
    }
}
