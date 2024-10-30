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

        $query = $this->export_log->export_parameters;

        CRUD::setModel($log->model);

        //Set donation type based on refferer
        if(str_contains($query['_http_referrer'],"find-donations-team")){
            $entries = $log->model::where('donation_type','=','team');
        }
        elseif(str_contains($query['_http_referrer'],"find-donations-direct")){
            $entries = $log->model::where('donation_type','=','direct');
        }
        else{
            $entries = $log->model::where('donation_type','=','individual');
        }

        //Accept parameters
        if(isset($query['f']) && !isset($query['rollover_campaign'])){
            $entries = $entries->whereBetween('created_at', [$query['f'], $query['r']. ' 23:59:59'])->get();
        }
        elseif(!isset($query['f']) && isset($query['rollover_campaign'])){
            $entries = $entries->where('rollover_campaign',$query['rollover_campaign'])->get();
        }
        elseif(isset($query['f']) && isset($query['rollover_campaign'])){
            $entries = $entries->whereBetween('created_at', [$query['f'], $query['r']. ' 23:59:59'])->where('rollover_campaign',$query['rollover_campaign'])->get();
        }
        else{
            $entries = $entries->get();
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
