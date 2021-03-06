<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Actions\AuditRow;
use App\Models\PlatUser;
use App\Models\PlatUserProfile;

use App\Models\RechargeGroup;
use App\Presenters\Admin\PlatUserPresenter;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class PlatUserProfileController extends Controller
{
    use ModelForm;
    protected $profile_id = null;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        $this->profile_id = $id;
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(PlatUserProfile::class, function (Grid $grid) {


            $grid->id('ID')->sortable();
            $grid->column('platuser.username', '商户账户');
            $grid->column('property', '商户性质')->display(function ($property) {
                return $property ? '企业': '个人';
            });
            $grid->column('role', '认证角色')->display(function ($role) {
                return $role == 1 ? '代理' :'商户';
            });
            $grid->column('auth_profile', '实名资料')->display(function () {
                return "<a  class='show-profile' onclick='showProfile({$this->id})' data-id='{$this->id}' data-type='realname'>".$this->realname. "<br/>".$this->idcard."<br/>手持证件照<br/>证件照背面<br/>证件照正面"."</a>";
            });
//            $grid->column('scope', '经营范围');
            $grid->column('enterprise_profile', '企业认证资料')->display(function (){
                return $this->property !=1 ? '-' : "<a onclick='showProfile({$this->id})' class='show-profile' data-id='{$this->id}' data-type='enterprise'>".$this->enterprise . "<br/>经营范围</a>";
            });
            $grid->column('platuser.status', '用户状态')->display(function ($status) {
                return PlatUserPresenter::showStatus($status);
            })->sortable();
            $grid->created_at('提交时间');
            $grid->disableCreation();
            $grid->actions(function ($actions) {
                $platuser = $this->row->platuser;
                if ($platuser['status'] == 0) {
                    $actions->append(new AuditRow(route('api.platuser.profiles.audit'), $this->getKey()));
                } elseif ($platuser['status'] == 1) {
                    $actions->append(new AuditRow(route('api.platuser.profiles.audit'), $this->getKey(), false, false));
                } elseif ($platuser['status'] == 2) {
                    $actions->append(new AuditRow(route('api.platuser.profiles.audit'), $this->getKey(), true, false));
                }

            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(PlatUserProfile::class, function (Form $form) {
            $platuser = PlatUserProfile::find($this->profile_id)->platuser;
            if ($platuser->status != 1) {
                Admin::script('updateButton();');
            }

            $form->tab('账户信息', function ($form) {
                $form->display('id', 'ID');
                $form->display('platuser.code', '账户编码');
                $form->select('property', '认证性质')->options(function ($property) {
                    return $property == 1 ? [1=>'企业'] : [0=>'个人'];
                })->readOnly();
                $form->select('role', '认证角色')->options(function ($role) {
                    return $role == 0 ? [0 => '商户'] : ($role == 1 ? [1 => '代理'] : [2 => '商务']);
                })->readOnly();
                $form->display('platuser.username', '账户名称');
                $form->display('realname', '实名认证');
                $form->display('idcard', '实名身份证');
                $form->display('enterprise', '企业名称');
                $form->display('license', '工商信息编码');
                $form->display('full_addr', '地址所在地');
                $form->display('created_at', '提交时间');
            })->tab('认证资料图片', function (Form $form) {
                $form->image('img_id_hand', '手持证件照')->readOnly();
                $form->image('img_id_front', '证件照正面')->readOnly();
                $form->image('img_id_back', '证件照背面')->readOnly();
                $form->image('img_license', '企业工商信息照')->readOnly();
                $form->image('img_tax', '税务信息照')->readOnly();
                $form->image('img_permit', '文网文附件照')->readOnly();
            });
            if ($platuser->status != 1) {
                $form->tab('修改风控信息', function (Form $form) {
                    $form->radio('platuser.is_withdraw', '允许提现')->options([
                        0 => '不允许',
                        1 => '允许'
                    ])->default(0)->readOnly();
                    $form->radio('platuser.settle_type', '结算类型')->options([
                        0 => '平台结算',
                        1 => 'api结算'
                    ]);
                    $form->radio('platuser.recharge_api', '支付api功能')->options([
                        0 => '未开通',
                        1 => '已开通'
                    ]);
                    $form->radio('platuser.settle_api', '结算api功能')->options([
                        0 => '未开通',
                        1 => '已开通'
                    ]);
                    $form->select('platuser.settle_cycle', '结算周期')->options([
                        0 => 't+0',
                        1 => 't+1',
                        7 => 't+7'
                    ]);
                    $form->select('platuser.recharge_mode', '交易模式')->options([
                        0 => '按个人',
                        1 => '按分组'
                    ])->default(1)->rules('required');
                    $form->select('platuser.recharge_gid', '交易分组')
                        ->options(function ($id) {
                            $group = RechargeGroup::find($id);
                            return RechargeGroup::where('classify', $group->classify)
                                ->orderBy('is_default', 'desc')
                                ->orderBy('is_default', 'desc')
                                ->pluck('name', 'id');
                        })->rules('required');

                });
            }
            $form->tools(function (Form\Tools $tools) use ($form) {
                $tools->disableBackButton();
                $tools->add("<input type='hidden' name='profile_id' value='{$this->profile_id}' />");
            });
            $form->ignore(['property', 'role']);
            $form->disableReset();
            if ($platuser->status == 1) {
                $form->disableSubmit();
            }
            $form->saving(function (Form $form) {
                $platuser = $form->model()->platuser;
                $platuser->update(array_merge(['status' => 1], $form->platuser));
            });
        });
    }

    public function showProfile($id)
    {
        $profile = PlatUserProfile::find($id);
        return view('admin.platuser.profile', compact('profile'));

    }
}
