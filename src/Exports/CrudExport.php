<?php

namespace RedSquirrelStudio\LaravelBackpackExportOperation\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class CrudExport implements FromView, ShouldAutoSize
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
     * @return View
     */
    public function view(): View
    {
        $log_model = config('backpack.operations.export.export_log_model');
        $log = $log_model::find($this->export_log->id);

//        $parts = parse_url($this->export_log->export_parameters['_http_referrer']);
//        parse_str($parts['query'], $query);
        $query = $this->export_log->export_parameters;

        CRUD::setModel($log->model);

        //Accept parameters
        if(isset($query['from_to']) && !isset($query['rollover_campaign'])){
            $query['from_to'] = json_decode($query['from_to']);
            $entries = $log->model::whereBetween('created_at', [$query['from_to']->from, $query['from_to']->to])->get();
        }
        elseif(!isset($query['from_to']) && isset($query['rollover_campaign'])){
            $entries = $log->model::where('rollover_campaign',$query['rollover_campaign'])->get();
        }
        elseif(isset($query['from_to']) && isset($query['rollover_campaign'])){
            $query['from_to'] = json_decode($query['from_to']);
            $entries = $log->model::whereBetween('created_at', [$query['from_to']->from, $query['from_to']->to])->where('rollover_campaign',$query['rollover_campaign'])->get();
        }
        else{
            $entries = $log->model::all();
        }

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
