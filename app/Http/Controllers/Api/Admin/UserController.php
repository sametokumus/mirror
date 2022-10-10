<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserType;
use App\Models\UserTypeDiscount;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class UserController extends Controller
{
    public function getUsers(){
        try {
            $users = User::query()
                ->leftJoin('user_profiles','user_profiles.user_id','=','users.id')
                ->selectRaw('users.*, user_profiles.name, user_profiles.surname')
                ->get();
            return response(['message' => 'İşlem başarılı.','status' => 'success','object' => ['users' => $users]]);
        } catch (QueryException $queryException){
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001','err' => $queryException->getMessage()]);
        }
    }

    public function getUserTypes(){
        try {
            $user_types = UserType::query()->where('active', 1)->get();
            return response(['message' => 'İşlem Başarılı.','status' => 'success','object' => ['user_types' => $user_types]]);
        } catch (QueryException $queryException){
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        }
    }

    public function getUserTypeById($id){
        try {
            $user_type = UserType::query()->where('id', $id)->first();
            return response(['message' => 'İşlem Başarılı.','status' => 'success','object' => ['user_type' => $user_type]]);
        } catch (QueryException $queryException){
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        }
    }
    public function addUserType(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required'
            ]);
            UserType::query()->insert([
                'name' => $request->name,
                'discount' => $request->discount
            ]);
            return response(['message' => 'Kullanıcı türü ekleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001','er' => $throwable->getMessage()]);
        }
    }
    public function updateUserType(Request $request,$id){
        try {
            $request->validate([
                'name' => 'required'
            ]);

            UserType::query()->where('id',$id)->update([
                'name' => $request->name,
                'discount' => $request->discount
            ]);

            return response(['message' => 'Kullanıcı türü güncelleme işlemi başarılı.','status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.','status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return  response(['message' => 'Hatalı işlem.','status' => 'error-001','ar' => $throwable->getMessage()]);
        }
    }
    public function deleteUserType($id){
        try {
            UserType::query()->where('id', $id)->update([
                'active' => 0
            ]);
            return response(['message' => 'Kullanıcı türü silme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.','status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        } catch (\Throwable $throwable) {
            return  response(['message' => 'Hatalı işlem.','status' => 'error-001','ar' => $throwable->getMessage()]);
        }
    }

    public function getUserTypeDiscounts(){
        try {
            $user_type_discounts = UserTypeDiscount::query()->where('active', 1)->get();
            return response(['message' => 'İşlem Başarılı.','status' => 'success','object' => ['user_type_discounts' => $user_type_discounts]]);
        } catch (QueryException $queryException){
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        }
    }

    public function getUserTypeDiscountById($id){
        try {
            $user_type_discount = UserTypeDiscount::query()->where('id', $id)->first();
            return response(['message' => 'İşlem Başarılı.','status' => 'success','object' => ['user_type_discount' => $user_type_discount]]);
        } catch (QueryException $queryException){
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001']);
        }
    }
    public function addUserTypeDiscount(Request $request)
    {
        try {
            $request->validate([
                'user_type' => 'required',
                'discount' => 'required',
                'brands' => 'required',
                'types' => 'required'
            ]);

            $brands = explode(',',$request->brands);
            $types = explode(',',$request->types);

            foreach ($brands as $brand){
                foreach ($types as $type){
                    UserTypeDiscount::query()->insert([
                        'user_type_id' => $request->user_type,
                        'discount' => $request->discount,
                        'brand_id' => $brand,
                        'type_id' => $type
                    ]);
                }
            }
            return response(['message' => 'Kullanıcı türüne göre indirim ekleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001','a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001','er' => $throwable->getMessage()]);
        }
    }
}
