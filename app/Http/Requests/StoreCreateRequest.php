<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

                'store_name'=>'required|unique:crm_store|max:255',
                'shopowner_mobile'=>'required|min:11|unique:crm_msg_shopowner|max:255',
                'shopowner_name'=>'required|unique:crm_msg_shopowner|max:50',
                'address'=>'required',
                'licenseH'=>'required',
                'licenseL'=>'required',
                'sales_id'=>'required'
        ];
    }
    public function messages()
    {
       return [
           'store_name.required' => '店铺名称不能为空',
           'shopowner_mobile.required' => '手机号不能为空',
           'shopowner_name.required' => '店主名称不能为空',
           'shopowner_mobile.min' => '手机号必须11位',
           'address.required'=>'店铺地址不能为空',
           'licenseH.required'=>'营业执照正面不能为空',
           'licenseL.required'=>'营业执照背面不能为空',
           'sales_id.required'=>'业物员不能为空'
       ];
    }
}
