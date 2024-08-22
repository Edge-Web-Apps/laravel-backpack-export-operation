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

        $query = $log->model::select(
            $log->config)
            ->cursor();

//        $query = $log->model::select(
//            'id',
//            'deal_id',
//            'dealer_id')
//            ->cursor();

        foreach ($query as $deal) {
            yield $log->config;
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
