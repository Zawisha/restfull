<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function addProduct(Request $request)
    {
        $name = $request->input('name');
        $price = $request->input('price');
        $category_ids = $request->input('category_ids');

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:30', 'min:2'],
            'price' => ['required', 'integer'],
            "category_ids"    => ['required','array','min:2','max:10'],
            "category_ids.*"  => ['required','integer','exists:categories,id'],
        ]);
        if ($validator->fails()) {
            $failed = $validator->messages();
            return response()->json([
                'messages' => $failed,
                'status' => 'fail'
            ], 200);
        }
        $product=  Product::create([
            'name' => $name,
            'price' => $price
        ]);


        foreach ($category_ids as $cat)
        {
            CategoryProduct::create([
                'category_id' => $cat,
                'product_id' => $product['id']
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' =>'товар успешно добавлен',
            'product' => $product,
        ], 200);
    }

    public function editProduct(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $price = $request->input('price');
        $published = $request->input('published');
        $deleted = $request->input('deleted');
        $category_ids = $request->input('category_ids');

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => ['required','integer','exists:products,id'],
            'name' => ['sometimes', 'string', 'max:30', 'min:2'],
            'price' => ['sometimes', 'integer'],
            'published' => ['sometimes', 'boolean'],
            'deleted' => ['sometimes', 'boolean'],
            "category_ids"    => ['sometimes','array','min:2','max:10'],
            "category_ids.*"  => ['required','integer','exists:categories,id'],
        ]);
        if ($validator->fails()) {
            $failed = $validator->messages();
            return response()->json([
                'messages' => $failed,
                'status' => 'fail'
            ], 200);
        }

        $product=Product::where('id', '=', $id)->get();
        if(!$name)
        {
           $name= $product[0]['name'];
        }
        if(!$price)
        {
            $price=$product[0]['price'];
        }
        if(!$published)
        {
            $published=false;
        }
        else
        {
            $published=true;
        }
        if(!$deleted)
        {
            $deleted=false;
        }
        else
        {
            $deleted=true;
        }
            Product::where('id', '=', $id)
                ->update([
                    'name' => $name,
                    'price' => $price,
                    'published' => $published,
                    'deleted' => $deleted
                    ]
                );

        if($category_ids)
        {
            CategoryProduct::where('product_id', '=', $id)->delete();
            foreach ($category_ids as $cat)
            {
                CategoryProduct::create([
                    'category_id' => $cat,
                    'product_id' => $id
                ]);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' =>'товар успешно обновлён',
            'product' => $product,
        ], 200);

    }

    public function deleteCategory(Request $request)
    {
        $id = $request->input('id');
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => ['required','integer','exists:categories,id'],
        ]);
        if ($validator->fails()) {
            $failed = $validator->messages();
            return response()->json([
                'messages' => $failed,
                'status' => 'fail'
            ], 200);
        }
        if (CategoryProduct::where('category_id', '=', $id)->exists()) {
            return response()->json([
                'message' => 'категория прикреплена к товару',
                'status' => 'fail'
            ], 200);
        }
        else
        {
            Category::where('id', '=', $id)->delete();
            return response()->json([
                'status' => 'success',
                'message' =>'категория удалена',
            ], 200);
        }

    }

    public function getProduct(Request $request)
    {
        $name = $request->input('name');
        $category_name = $request->input('category_name');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');
        $published = $request->input('published');

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => ['sometimes', 'string', 'max:30', 'min:2'],
            'category_name' => ['sometimes', 'string', 'max:30', 'min:2'],
            'price_from' => ['sometimes', 'integer'],
            'price_to' => ['sometimes', 'integer'],
            'published' => ['sometimes', 'boolean'],
        ]);
        if ($validator->fails()) {
            $failed = $validator->messages();
            return response()->json([
                'messages' => $failed,
                'status' => 'fail'
            ], 200);
        }
        if($price_from&&$price_to)
        {
            $price_flag=true;
        }
        else
        {
            $price_flag=false;
        }
        if($published==null)
        {
            $published_flag=false;
        }
        else
        {
            $published_flag=true;
        }

        $product=Product::with('categories_table')
            ->when($name , function ($query)use ($name){return $query ->where('name', $name);})
            ->when($category_name , function ($query)use ($category_name){return $query ->whereHas('categories_table', function ($query)use ($category_name) {
                return $query->where('name', '=', $category_name);
            });})
            ->when($price_flag , function ($query)use ($price_from,$price_to){return $query ->whereBetween('price', [$price_from, $price_to]);})
            ->when($published_flag , function ($query)use ($published){return $query ->where('published', $published);})
            ->where('deleted', false)
            ->get();
        return response()->json([
            'status' => 'success',
            'message' =>'товар получен',
            'product' => $product,
        ], 200);

    }

    public function addCategory(Request $request)
    {
        $name = $request->input('name');

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:30', 'min:2'],
        ]);
        if ($validator->fails()) {
            $failed = $validator->messages();
            return response()->json([
                'messages' => $failed,
                'status' => 'fail'
            ], 200);
        }
        $product=  Category::create([
            'name' => $name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' =>'категория успешно добавлена',
            'product' => $product,
        ], 200);
    }


}
