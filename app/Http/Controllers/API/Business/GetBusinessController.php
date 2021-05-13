<?php


namespace App\Http\Controllers\API\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessDataResources;
use Illuminate\Support\Facades\Redis;
use App\Models\BusinessData;
use PDOException;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
class GetBusinessController extends Controller
{

    //获取星级商户列表
    public function getStarBusinessList(Request $request){

//        $re = Redis::set('key1','1231231');
//        var_dump($re);
//        var_dump(Redis::get('key1'));
        $page = $request->input('page');
        $pageSize = $request->input('pageSize',10);

        $category = $request->input("category");
        $keyword = $request->input('keyword');
        $city = $request->input('city');
        $district = $request->input('district');
        $is_recommend = $request->input('is_recommend');

        $data = (new BusinessData())
            ->when($category,function($query) use ($category) {
                return $query->where('category_id', $category);
            })
            ->when($keyword,function($query) use ($keyword) {
                return $query->where('name', 'like', "%" . $keyword . "%");
            })
            ->when($city,function($query) use ($city) {
                $cityId = CityData::where("name", $city)->value("code");

                if($cityId)
                {
                    $query->where('city', $cityId);
                }
            })
            ->when($district,function($query) use ($city, $district) {
                $cityId = CityData::where("name", $city)->value("code");
                if($cityId)
                {
                    $districtId = CityData::where("name", $district)
                        ->where("p_code", $cityId)
                        ->value("code");
                    $query->where('district', $districtId);
                }
            })
            ->where("status", 1);
        if($is_recommend==1){
            $data=$data
                ->where('is_recommend', 1);
        }

        $data=$data
            ->with(['businessApply'])
            ->orderBy('is_recommend', 'desc')
            ->orderBy('sort', 'desc')
            ->latest('id')
            ->forPage($page, $pageSize)
            ->get();

        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => $data]);

    }

    //获取星级商户列表-商家页-分类筛选搜索
    public function getAllBusinessList(Request $request){

        $category = $request->input("category");
        $keyword = $request->input('keyword');
        $city = $request->input('city');
        $district = $request->input('district');

        $page = $request->input('page');
        $pageSize = $request->input('pageSize',10);
        $data = (new BusinessData())
            ->where("status", 1)
            ->where('is_recommend', 1)
            ->with(['businessApply'])
            ->orderBy('is_recommend', 'desc')
            ->orderBy('sort', 'desc')
            ->latest('id')
            ->forPage($page, $pageSize)
            ->get();

        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => $data]);

    }




}
