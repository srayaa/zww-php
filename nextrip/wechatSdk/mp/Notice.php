<?php

namespace WechatSdk\mp;

/**
 * 模板消息
 */
class Notice {

	/**
	 * Http对象
	 *
	 * @var Http
	 */
	protected $http;

	/**
	 * 默认数据项颜色
	 *
	 * @var string
	 */
	protected $defaultColor = '#173177';

	/**
	 * 消息属性
	 *
	 * @var array
	 */
	protected $message = array(
			'touser' => '',
			'template_id' => '',
			'url' => '',
			'topcolor' => '#FF00000',
			'data' => array(),
	);

	/**
	 * 工业列表
	 *
	 * @var array
	 */
	protected static $industries = array(
			'IT科技' => array(
					1 => '互联网/电子商务',
					2 => 'IT软件与服务',
					3 => 'IT硬件与设备',
					4 => '电子技术',
					5 => '通信与运营商',
					6 => '网络游戏',
			),
			'金融业' => array(
					7 => '银行',
					8 => '基金|理财|信托',
					9 => '保险',
			),
			'餐饮' => array(
					10 => '餐饮',
			),
			'酒店旅游' => array(
					11 => '酒店',
					12 => '旅游',
			),
			'运输与仓储' => array(
					13 => '快递',
					14 => '物流',
					14 => '仓储',
			),
			'教育' => array(
					16 => '培训',
					17 => '院校',
			),
			'政府与公共事业' => array(
					18 => '学术科研',
					19 => '交警',
					20 => '博物馆',
					21 => '公共事业|非盈利机构',
			),
			'医药护理' => array(
					22 => '医药医疗',
					23 => '护理美容',
					24 => '保健与卫生',
			),
			'交通工具' => array(
					25 => '汽车相关',
					26 => '摩托车相关',
					27 => '火车相关',
					28 => '飞机相关',
			),
			'房地产' => array(
					29 => '建筑',
					30 => '物业',
			),
			'消费品' => array(
					31 => '消费品',
			),
			'商业服务' => array(
					32 => '法律',
					33 => '会展',
					34 => '中介服务',
					35 => '认证',
					36 => '审计',
			),
			'文体娱乐' => array(
					37 => '传媒',
					38 => '体育',
					39 => '娱乐休闲',
			),
			'印刷' => array(
					40 => '印刷',
			),
			'其它' => array(
					41 => '其它',
			),
	);

	const API_SEND_NOTICE = 'https://api.weixin.qq.com/cgi-bin/message/template/send';
	const API_SET_INDUSTRY = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry';
	const API_ADD_TEMPLATE = 'https://api.weixin.qq.com/cgi-bin/template/api_add_template';

	/**
	 * constructor
	 *
	 * @param string $appId
	 * @param string $appSecret
	 */
	public function __construct($appId, $appSecret) {
		$this->http = new Http(new AccessToken($appId, $appSecret));
	}

	/**
	 * 修改账号所属行业
	 *
	 * @param int $industryOne
	 * @param int $industryTwo
	 *
	 * @return bool
	 */
	public function setIndustry($industryOne, $industryTwo) {
		$params = array(
				'industry_id1' => $industryOne,
				'industry_id2' => $industryTwo,
		);

		return $this->http->jsonPost(self::API_SET_INDUSTRY, $params);
	}

	/**
	 * 添加模板并获取模板ID
	 *
	 * @param string $shortId
	 *
	 * @return string
	 */
	public function addTemplate($shortId) {
		$params = array(
				'template_id_short' => $shortId,
		);

		$result = $this->http->jsonPost(self::API_SET_INDUSTRY, $params);

		return $result['template_id'];
	}

	/**
	 * @param string $to
	 * @param string $templateId
	 * @param array  $data
	 * @param string $url
	 * @param string $color
	 *
	 * @return int
	 */
	public function send(
	$to = null, $templateId = null, array $data = array(), $url = null, $color = '#FF0000'
	) {
		$params = array(
				'touser' => $to,
				'template_id' => $templateId,
				'url' => $url,
				'topcolor' => $color,
				'data' => $data,
		);

		foreach ($params as $key => $value) {
			if (empty($value) && empty($this->message[$key])) {
				throw new Exception("消息属性 '$key' 不能为空！");
			}

			$params[$key] = empty($value) ? $this->message[$key] : $value;
		}

		$params['data'] = $this->formatData($params['data']);

		$result = $this->http->jsonPost(self::API_SEND_NOTICE, $params);

		return $result['msgid'];
	}

	/**
	 * 设置模板消息数据项的默认颜色
	 *
	 * @param string $color
	 */
	public function defaultColor($color) {
		$this->defaultColor = $color;
	}

	/**
	 * 行业列表
	 *
	 * @return array
	 */
	public function industries() {
		return self::$industries;
	}

	/**
	 * 魔术访问
	 *
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function __get($property) {
		if ($property === 'industries') {
			return $this->industries();
		}
	}

	/**
	 * 格式化模板数据
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function formatData($data) {
		$return = array();

		foreach ($data as $key => $item) {
			if (is_scalar($item)) {
				$value = $item;
				$color = $this->defaultColor;
			} elseif (is_array($item) && !empty($item)) {
				if (isset($item['value'])) {
					$value = strval($item['value']);
					$color = empty($item['color']) ? $this->defaultColor : strval($item['color']);
				} elseif (count($item) < 2) {
					$value = array_shift($item);
					$color = $this->defaultColor;
				} else {
					list($value, $color) = each($item);
				}
			} else {
				$value = '数据项格式错误';
				$color = $this->defaultColor;
			}

			$return[$key] = array(
					'value' => $value,
					'color' => $color,
			);
		}

		return $return;
	}

	/**
	 * 魔术调用
	 *
	 * @param string $method
	 * @param array  $args
	 *
	 * @return Notice
	 */
	public function __call($method, $args) {
		$map = array(
				'template' => 'template_id',
				'uses' => 'template_id',
				'templateId' => 'template_id',
				'to' => 'touser',
				'receiver' => 'touser',
				'color' => 'topcolor',
				'topColor' => 'topcolor',
				'url' => 'url',
				'linkTo' => 'linkTo',
				'data' => 'data',
				'with' => 'data',
		);

		if (0 === stripos($method, 'with')) {
			$method = lcfirst(substr($method, 4));
		}

		if (0 === stripos($method, 'and')) {
			$method = lcfirst(substr($method, 3));
		}

		if (isset($map[$method])) {
			$this->message[$map[$method]] = array_shift($args);

			return $this;
		}
	}

}
