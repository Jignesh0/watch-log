<?php


namespace nineBrainz\logs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class LogController extends Controller
{
    public function index()
    {
          $availableDates = $this->getLogFileDates();
          if (count($availableDates) == 0) {
            return response()->json([
              'success' => false,
              'message' => 'No log available'
            ]);
          }

          $date = 'laravel-'.date('Y-m-d').'.log';
          $configDate = $date;
          if ($configDate == null) {
            $configDate = $availableDates[0];
          }

          if (!in_array($configDate, $availableDates)) {

            return response()->json([
              'success' => false,
              'message' => 'No log file found with selected date ' . $configDate
            ]);
          }


          $pattern = "/^\[(?<date>.*)\]\s(?<env>\w+)\.(?<type>\w+):(?<message>.*)/m";

          $fileName =  $configDate;
          $content = file_get_contents(storage_path('logs/' . $fileName));
          preg_match_all($pattern, $content, $matches, PREG_SET_ORDER, 0);

          $logs = [];
          foreach ($matches as $match) {
            $logs[] = [
              'timestamp' => $match['date'],
              'env' => $match['env'],
              'type' => $match['type'],
              'message' => trim($match['message']),
              'log_date' => date('Y-m-d',strtotime($match['date'])),
            ];
          }


          $date = $fileName;

          $data = collect([
            'available_log_dates' => $availableDates,
            'date' => $date,
            'filename' => $fileName,
            'logs' => $logs
          ]);

        return view('logs::log', compact('data'));
  }


  public function getLogFileDates()
  {
        $dates = [];
        $files = glob(storage_path('logs/*.log'));

        $files = array_reverse($files);
        foreach ($files as $path) {
            if(basename($path) != 'laravel.log'){
                $fileName = basename($path);
                array_push($dates, $fileName);
            }
        }
        return $dates;
  }

  //----------------------------------------------------Datatable log list--------------------------------------------------//
  public function logList(Request $request)
  {
            $availableDates = $this->getLogFileDates();
              if (count($availableDates) == 0) {
                return response()->json([
                  'success' => false,
                  'message' => 'No log available'
                ]);
              }

              $date_from = $request->date_from;

              if(isset($date_from)){
                $serchDate = substr($date_from, strpos($date_from, 'laravel') + 8, 10);
                $configDate = 'laravel-'.$serchDate.'.log';
              }else{
                $configDate = 'laravel-'.date('Y-m-d').'.log';

              }
              if ($configDate == null) {
                $configDate = $availableDates[0];
              }

              if (!in_array($configDate, $availableDates)) {
                return response()->json([
                  'success' => false,
                  'message' => 'No log file found with selected date ' . $configDate
                ]);
              }
              $pattern = "/^\[(?<date>.*)\]\s(?<env>\w+)\.(?<type>\w+):(?<message>.*)/m";

              $fileName =  $configDate;
              $content = file_get_contents(storage_path('logs/' . $fileName));
              preg_match_all($pattern, $content, $matches, PREG_SET_ORDER, 0);

              $logs = [];
              foreach ($matches as $match) {
                $logs[] = [
                  'timestamp' => $match['date'],
                  'env' => $match['env'],
                  'type' => $match['type'],
                  'message' => trim($match['message']),
                  'log_date' => date('Y-m-d',strtotime($match['date'])),
                ];
              }

              $date = $fileName;
              $dataList = collect([
                'available_log_dates' => $availableDates,
                'date' => $date,
                'filename' => $fileName,
                'logs' => $logs
              ]);

              $columns = array(
                0 => 'level',
                1 => 'context',
                2 => 'date',
                3 => 'description',
              );


              $limit = $request->input('length');
              $start = $request->input('start'); //offset
              $order = $columns[$request->input('order.0.column')];
              $dir = $request->input('order.0.dir');

              $totalDataRecord = count($dataList['logs']);
              $totalFiltered =  $totalDataRecord;
              $todayDate = Carbon::now()->format('Y-m-d');
              if (empty($request->input('search.value'))) {

                if(isset($date_from)){
                  $logCollection = collect($dataList['logs'])
                  ->where('log_date',$serchDate);
                }else{
                  $logCollection = collect($dataList['logs'])
                  ->where('log_date',$todayDate);
                }

                if(isset($request->type)){
                    $type = $request->type;
                    $logList = $logCollection->filter(function ($item) use ($type) {
                    return stripos($item['type'], $type) !== false;
                    })->sortByDesc('timestamp')->toArray();
                    $totalFiltered = count($logList);
                }else{
                    $logCollection = $logCollection->sortByDesc('timestamp')->toArray();
                    $logList = array_slice($logCollection, $start, $limit); //offset and limit
                }


              } else { //search query

                $search = $request->input('search.value');
                $logCollection = collect($dataList['logs']);

                if(isset($date_from)){
                    $logCollection =$logCollection->where('log_date',$serchDate);
                }
                //query
                $logList = $logCollection->filter(function ($item) use ($search) {
                  return stripos($item['message'], $search) !== false;
                });

                if(isset($request->type)){
                    $type = $request->type;
                    $logList = $logList->filter(function ($item) use ($type) {
                    return stripos($item['type'], $type) !== false;
                    })->sortByDesc('timestamp')->toArray();
                    $totalFiltered = count($logList);
                }else{
                    $logCollection = $logList->sortByDesc('timestamp')->toArray();
                    $totalFiltered = count($logList);
                }
              }

              $data = array();
              if (!empty($logList)) {
                $type = ['ERROR' => 'red', 'INFO' => '#5dbbd0', 'WARNING' => 'orange', 'ALERT' => '#e818d1', 'DEBUG' => '#1825e8', 'EMERGENCY' => '#040404'];
                foreach ($logList as $value) {
                  $levelColor = isset($value['type']) ? $type[$value['type']] : '';
                  $nestedData['level'] = "<span class='badge' style='background-color:{$levelColor}'>{$value['type']}</span>";
                  $nestedData['context'] = $value['env'];
                  $nestedData['date'] = $value['timestamp'];
                  $nestedData['description'] = "<div class='dec'>".$value['message']."</div>";
                  $data[] = $nestedData;
                }
              }

              $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalDataRecord),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
              );

            echo json_encode($json_data);
  }
}
