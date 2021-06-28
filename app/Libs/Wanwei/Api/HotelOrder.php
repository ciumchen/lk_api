<?php

namespace Wanwei\Api;

use Exception;

class HotelOrder extends RequestBase
{
    
    /**
     * Description:
     *
     * @param  string  $page        页码
     * @param  string  $limit       每页条数，最大30条
     * @param  string  $cityName    城市
     * @param  string  $inDate      入住时间，格式为：YYYY-MM-DD（默认2天后）
     * @param  string  $outDate     离开时间，格式为：YYYY-MM-DD（默认3天后）
     * @param  string  $sortKey     排序规则(默认recommend.推荐值排序)
     *                              recommend:推荐值降序
     *                              satisfaction :口碑
     *                              price-asc:起价升序
     *                              price-desc:起价降序
     * @param  string  $star        星级
     *                              TWO:二星级,
     *                              THREE:三星级,
     *                              FOUR:四星级,
     *                              FIVE:五星级,
     *                              BUDGET:经济型,
     *                              CONFORT:舒适型,
     *                              HIGHEND:高档型,
     *                              LUXURY:豪华型【多个以逗号:‘,’分隔】
     * @param  string  $minPrice    房价最低价
     * @param  string  $maxPrice    房价最高价
     * @param  string  $poiKey      区域关键字
     *                              可以使用关键字搜索中的displayName
     *                              （poiKey、poiCode、longitude、latitude四个值需结合使用）
     * @param  string  $poiCode     经纬度对应的编号
     *                              poi类型值：
     *                              1-城市，
     *                              2-行政区，
     *                              3-商圈，
     *                              4-景点，
     *                              7-酒店，
     *                              12-机场，
     *                              13-地铁，
     *                              14-火车站
     *                              （poiKey、poiCode、longitude、latitude四个值需结合使用）
     * @param  string  $longitude   经度（poiKey、poiCode、longitude、latitude四个值需结合使用）
     * @param  string  $latitude    维度（poiKey、poiCode、longitude、latitude四个值需结合使用）
     * @param  string  $keyWords    搜索关键词
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/22 0022
     */
    public function hotelSearch(
        $page,
        $limit,
        $cityName = '',
        $inDate = '',
        $outDate = '',
        $sortKey = '',
        $star = '',
        $minPrice = '',
        $maxPrice = '',
        $poiKey = '',
        $poiCode = '',
        $longitude = '',
        $latitude = '',
        $keyWords = ''
    ) {
        $apiMethod = '1653-1';/* 接口标识 */
        $params = [
            'page'      => $page,
            'limit'     => $limit,
            'cityName'  => $cityName,
            'inDate'    => $inDate,
            'outDate'   => $outDate,
            'sortKey'   => $sortKey,
            'star'      => $star,
            'minPrice'  => $minPrice,
            'maxPrice'  => $maxPrice,
            'poiKey'    => $poiKey,
            'poiCode'   => $poiCode,
            'longitude' => $longitude,
            'latitude'  => $latitude,
            'keyWords'  => $keyWords,
        ];
        try {
            $ShowApi = $this->getShowApi($apiMethod);
            foreach ($params as $key => $val) {
                if (!empty($val)) {
                    $ShowApi->addTextPara($key, $val);
                }
            }
            $response = $ShowApi->post();
            $result = $this->fetchResult($response->getContent());
            if (!array_key_exists('data', $result)) {
                if (array_key_exists('ret_code', $result) && $result[ 'ret_code' ] != '0') {
                    throw new Exception($result[ 'remark' ].'--'.json_encode($result));
                }
                throw  new Exception('酒店获取失败：'.json_encode($result));
            }
            return $result[ 'data' ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:获取酒店支持的城市
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/25 0025
     */
    public function getStandByCity()
    {
        $apiMethod = '1653-2';/* 接口标识 */
        try {
            $ShowApi = $this->getShowApi($apiMethod);
            $response = $ShowApi->post();
            $result = $this->fetchResult($response->getContent());
            if (!array_key_exists('cityNameList', $result)) {
                if (array_key_exists('ret_code', $result) && $result[ 'ret_code' ] != '0') {
                    throw new Exception($result[ 'remark' ].'--'.json_encode($result));
                }
                throw  new Exception('酒店支持城市获取失败：'.json_encode($result));
            }
            return $result[ 'cityNameList' ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:获取酒店详情
     *
     * @param  string  $hotelId
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/25 0025
     */
    public function getHotelDetails($hotelId)
    {
        $apiMethod = '1653-3';/* 接口标识 */
        try {
            $ShowApi = $this->getShowApi($apiMethod);
            $ShowApi->addTextPara('hotelId', $hotelId);
            $response = $ShowApi->post();
            $result = $this->fetchResult($response->getContent());
            if (!array_key_exists('data', $result)) {
                if (array_key_exists('ret_code', $result) && $result[ 'ret_code' ] != '0') {
                    throw new Exception($result[ 'remark' ].'--'.json_encode($result));
                }
                throw  new Exception('酒店支持城市获取失败：'.json_encode($result));
            }
            return $result[ 'data' ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description: 获取酒店信息
     *
     * @param  string  $hotelId     酒店ID
     * @param  string  $inDate      入住时间，格式为：YYYY-MM-DD（默认2天后）
     * @param  string  $outDate     离开时间，格式为：YYYY-MM-DD（默认3天后）
     * @param  string  $excludeOta  排除禁止OTA裸售的数据，默认true
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/25 0025
     */
    public function getHotelRooms($hotelId, $inDate = '', $outDate = '', $excludeOta = 'true')
    {
        $apiMethod = '1653-4';/* 接口标识 */
        $excludeOta = (empty($excludeOta) || $excludeOta != 'false') ? 'true' : 'false';
        $params = [
            'hotelId'    => $hotelId,
            'inDate'     => $inDate,
            'outDate'    => $outDate,
            'excludeOta' => $excludeOta,
        ];
        try {
            $ShowApi = $this->getShowApi($apiMethod);
            foreach ($params as $key => $val) {
                if (!empty($val)) {
                    $ShowApi->addTextPara($key, $val);
                }
            }
            $response = $ShowApi->post();
            $result = $this->fetchResult($response->getContent());
            if (!array_key_exists('roomInfo', $result)) {
                if (array_key_exists('ret_code', $result) && $result[ 'ret_code' ] != '0') {
                    throw new Exception($result[ 'remark' ].'--'.json_encode($result));
                }
                throw  new Exception('房间信息获取失败：'.json_encode($result));
            }
            return $result[ 'roomInfo' ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:
     *
     * @param  string  $hotelId        酒店ID
     * @param  string  $roomId         价格计划id
     * @param  string  $numberOfRooms  预订房间数
     * @param  string  $inDate         入店时间
     * @param  string  $outDate        离店时间
     * @param  string  $child          入住儿童数
     * @param  string  $man            成人数
     * @param  string  $childAges      入住儿童的年龄数，注意如果有多名儿童的年龄请用,分隔
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/25 0025
     */
    public function getRoomPrice($hotelId, $roomId, $numberOfRooms, $inDate, $outDate, $child, $man, $childAges = '')
    {
        $apiMethod = '1653-5';/* 接口标识 */
        $params = [
            'hotelId'       => $hotelId,
            'roomId'        => $roomId,
            'numberOfRooms' => $numberOfRooms,
            'inDate'        => $inDate,
            'outDate'       => $outDate,
            'child'         => $child,
            'man'           => $man,
            'childAges'     => $childAges,
        ];
        try {
            $ShowApi = $this->getShowApi($apiMethod);
            foreach ($params as $key => $val) {
                if (!empty($val)) {
                    $ShowApi->addTextPara($key, $val);
                }
            }
            $response = $ShowApi->post();
            $result = $this->fetchResult($response->getContent());
            if (!array_key_exists('data', $result)) {
                if (array_key_exists('ret_code', $result) && $result[ 'ret_code' ] != '0') {
                    throw new Exception($result[ 'remark' ].'--'.json_encode($result));
                }
                throw  new Exception('房间价格信息获取失败：'.json_encode($result));
            }
            return $result[ 'data' ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:
     *
     * @param  string  $customerName        入住人信息，每个房间仅需填写1人。【多个人代表多个房间、使用逗号‘,’分隔】
     * @param  string  $ratePlanId          价格计划Id
     * @param  string  $hotelId             酒店ID
     * @param  string  $contactName         联系人姓名
     * @param  string  $contactPhone        联系人手机号码（酒店确认短信以及入住凭证号会发给这个手机号）
     * @param  string  $inDate              入住时间，格式为：YYYY-MM-DD
     * @param  string  $outDate             离开时间，格式为：YYYY-MM-DD
     * @param  string  $man                 入住成人数，需和实施询价时填的一样
     * @param  string  $customerArriveTime  客户到达时间 格式HH:mm:ss 例如09:20:30 表示早上9点20分30秒
     * @param  string  $specialRemarks      特殊需求 可传入多个，格式：2,8。
     *                                      0 无要求
     *                                      2 尽量安排无烟房
     *                                      8 尽量安排大床 仅当床型为“X张大床或X张双床”时，此选项才有效
     *                                      10 尽量安排双床房 仅当床型为“X张大床或X张双床”时，此选项才有效
     *                                      11 尽量安排吸烟房
     *                                      12 尽量高楼层
     *                                      15 尽量安排有窗房
     *                                      16 尽量安排安静房间
     *                                      18 尽量安排相近房间
     * @param  string  $contactEmail        联系人邮箱
     * @param  string  $childNum            入住儿童数，与实时询价时提交的应一致
     * @param  string  $childAges           入住儿童的年龄，多个年龄用,分隔，与实时询价时提交的应一致
     * @param  string  $callback            回调地址(需http开头，部分较高版本的ssl协议无法正常回调)，
     *                                      请求方式POST，
     *                                      回调参数
     *                                      order(订单id)，
     *                                      oldStatus(原订单状态)，
     *                                      newStatus(新的订单状态)，
     *                                      project(项目名，这里值为hotel)
     *                                      只回调一次，不验证您的返回状态和参数
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/28 0028
     */
    public function setHotelOrder(
        $customerName,
        $ratePlanId,
        $hotelId,
        $contactName,
        $contactPhone,
        $inDate,
        $outDate,
        $man,
        $customerArriveTime,
        $specialRemarks = '',
        $contactEmail = '',
        $childNum = '',
        $childAges = '',
        $callback = ''
    ) {
        $apiMethod = '1653-6';/* 接口标识 */
        $params = [
            'customerName'       => $customerName,
            'ratePlanId'         => $ratePlanId,
            'hotelId'            => $hotelId,
            'contactName'        => $contactName,
            'contactPhone'       => $contactPhone,
            'inDate'             => $inDate,
            'outDate'            => $outDate,
            'man'                => $man,
            'customerArriveTime' => $customerArriveTime,
            'specialRemarks'     => $specialRemarks,
            'contactEmail'       => $contactEmail,
            'childNum'           => $childNum,
            'childAges'          => $childAges,
            'callback'           => $callback,
        ];
        try {
            $ShowApi = $this->getShowApi($apiMethod);
            foreach ($params as $key => $val) {
                if (!empty($val)) {
                    $ShowApi->addTextPara($key, $val);
                }
            }
            $response = $ShowApi->post();
            $result = $this->fetchResult($response->getContent());
            if (!array_key_exists('orderId', $result)) {
                if (array_key_exists('ret_code', $result) && $result[ 'ret_code' ] != '0') {
                    throw new Exception($result[ 'remark' ].'--'.json_encode($result));
                }
                throw  new Exception('订单创建失败：'.json_encode($result));
            }
            return $result[ 'orderId' ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
