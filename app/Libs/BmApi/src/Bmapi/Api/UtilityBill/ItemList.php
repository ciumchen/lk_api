<?php

namespace Bmapi\Api\UtilityBill;

use Bmapi\core\ApiRequest;

/**
 * 查询水电煤类标准商品列表
 * Class ItemList
 *
 * @package Bmapi\Api\UtilityBill
 */
class ItemList extends ApiRequest
{

    /**
     * @var string 接口名称
     */
    protected $method = 'bm.elife.directRecharge.waterCoal.item.list';

    /**
     * @var array 接口参数
     */
    private $paramsKey = [
        'pageNo',
        'pageSize',
        'provinceVid',
        'cityVid',
        'projectId',
        'itemName',
        'city',
        'province',
    ];

    /**
     * @var int 页码 从0开始
     */
    private $pageNo = 0;

    /**
     * @var int 单页返回的记录数
     */
    private $pageSize = 10;

    /**
     * @var string 省属性v编号
     */
    private $provinceVid;

    /**
     * @var string 市属性v编号
     */
    private $cityVid;

    /**
     * @var string 缴费项目编号，水费c2670，电费c2680，气费c2681；
     */
    private $projectId;

    /**
     * @var string 标准商品名称,支持不带特殊字符的模糊匹配；
     */
    private $itemName;

    /**
     * @var string 市名称(后面不带"市")
     */
    private $city;

    /**
     * @var string 省名称 (后面不带"省")
     */
    private $province;

    /**
     * @return array
     */
    public function apiParams()
    : array
    {
        $params = [];
        foreach ($this->paramsKey as $k) {
            if (isset($this->$k)) {
                $params[ $k ] = $this->$k;
            }
        };
        return array_merge(parent::apiParams(), $params);
    }

    /**
     * 设置页码
     *
     * @param int $val
     *
     * @return $this
     */
    public function setPageNo($val)
    {
        $this->pageNo = $val;
        return $this;
    }

    /**
     * 设置单页数据条数
     *
     * @param int $val
     *
     * @return $this
     */
    public function setPageSize($val)
    {
        $this->pageSize = $val;
        return $this;
    }

    /**
     * 设置省属性v编号
     *
     * @param string $val
     *
     * @return $this
     */
    public function setProvinceVid($val)
    {
        $this->provinceVid = $val;
        return $this;
    }

    /**
     * 设置市属性v编号
     *
     * @param string $val
     *
     * @return $this
     */
    public function setCityVid($val)
    {
        $this->cityVid = $val;
        return $this;
    }

    /**
     * 设置缴费项目编号，水费c2670，电费c2680，气费c2681；
     *
     * @param string $val
     *
     * @return $this
     */
    public function setProjectId($val)
    {
        $this->projectId = $val;
        return $this;
    }

    /**
     * 设置标准商品名称,支持不带特殊字符的模糊匹配
     *
     * @param string $val
     *
     * @return $this
     */
    public function setItemName($val)
    {
        $this->itemName = $val;
        return $this;
    }

    /**
     * 设置市名称(后面不带"市")
     *
     * @param string $val
     *
     * @return $this
     */
    public function setCity($val)
    {
        $this->city = $val;
        return $this;
    }

    /**
     * 设置省名称(后面不带"省")
     *
     * @param string $val
     *
     * @return $this
     */
    public function setProvince($val)
    {
        $this->province = $val;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageNo()
    {
        return $this->pageNo;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @return string
     */
    public function getProvinceVid()
    {
        return $this->provinceVid;
    }

    /**
     * @return string
     */
    public function getCityVid()
    {
        return $this->cityVid;
    }

    /**
     * @return string
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @return string
     */
    public function getItemName()
    {
        return $this->itemName;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getProvince()
    {
        return $this->province;
    }
}
