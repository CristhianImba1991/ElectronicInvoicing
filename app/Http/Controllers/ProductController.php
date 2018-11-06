<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\Product;
use Illuminate\Http\Request;


use ElectronicInvoicing\{Company, Branch, IvaTax, IceTax, IrbpnrTax, ProductTax};
use ElectronicInvoicing\Http\Requests\StoreProductRequest;
use ElectronicInvoicing\Rules\{ValidRUC, ValidSign};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Image;
use Storage;
use Validator;



class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $user = Auth::user();
        if ($user->hasPermissionTo('delete_hard_products')) {
            $products = Product::withTrashed()->get()->sortBy(['main_code']);
        } else {
            $products = Product::all()->sortBy(['main_code']);
        }
        return view('products.index', compact('products'));

    }


    public function create()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
        $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
        $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
        return view('products.create',compact(['companies','iva_taxes','ice_taxes','irbpnr_taxes']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        Validator::make($request->all(), [
            'code' => 'uniquemultiple:emission_points,branch_id,' . $request->branch . ',code,' . $request->code
        ], array('uniquemultiple' => 'The :attribute has already been taken.'))->validate();
        $input = $request->except(['company', 'branch']);
        $input['branch_id'] = $request->branch;
        $product = Product::create($input);
        $input_product_taxes['product_id']=$product->id;
        $input_product_taxes['iva_tax_id']=$request->iva_tax;
        $input_product_taxes['ice_tax_id']=$request->ice_tax;
        $input_product_taxes['irbpnr_tax_id']=$request->irbpnr_tax;
        $product = ProductTax::create($input_product_taxes);
        return redirect()->route('products.index')->with(['status' => 'Products added successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \ElectronicInvoicing\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(StoreProductRequest $request, Product $product)
    {
        $product->fill($request->except(['company', 'branch']))->save();
        return redirect()->route('products.index')->with(['status' => 'Product updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    public function delete(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with(['status' => 'Product deactivated successfully.']);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function taxes(Request $request) {
        if (is_array($request->id)) {
            $taxes = ProductTax::whereIn('product_id', $request->id)->with('product')->get();
            for ($i = 0; $i < count($taxes); $i++) {
                $taxes[$i]['iva'] = IvaTax::find($taxes[$i]['iva_tax_id']);
                $taxes[$i]['ice'] = IceTax::find($taxes[$i]['ice_tax_id']);
                $taxes[$i]['irbpnr'] = IrbpnrTax::find($taxes[$i]['irbpnr_tax_id']);
            }
            return $taxes->toJson();
        } else if (is_string($request->id)) {
            $taxes = ProductTax::where('product_id', $request->id)->with('product')->get();
            for ($i = 0; $i < count($taxes); $i++) {
                $taxes[$i]['iva'] = IvaTax::find($taxes[$i]['iva_tax_id']);
                $taxes[$i]['ice'] = IceTax::find($taxes[$i]['ice_tax_id']);
                $taxes[$i]['irbpnr'] = IrbpnrTax::find($taxes[$i]['irbpnr_tax_id']);
            }
            return $taxes->toJson();
        }
    }
}
