<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\Product;
use Illuminate\Http\Request;


use ElectronicInvoicing\{Company, Branch, IvaTax, IceTax, IrbpnrTax, ProductTax};
use ElectronicInvoicing\Rules\{ValidRUC, ValidSign};
use ElectronicInvoicing\StaticClasses\ValidationRule;
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
     * Validate the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function validateRequest(Request $request, Product $product = NULL)
    {
        $validator = Validator::make($request->all(), ValidationRule::makeRule('product', $request));
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $product);
                $request->session()->flash('status', trans_choice(__('message.model_updated_successfully', ['model' => trans_choice(__('view.product'), 0)]), 0));
            } else {
                $this->store($request);
                $request->session()->flash('status', trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.product'), 0)]), 0));
            }
        }
        return json_encode(array("status" => $isValid, "messages" => $validator->messages()->messages()));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $branches = Branch::all();
        } else {
            $branches = CompanyUser::getBranchesAllowedToUser($user);
        }
        $products = array();
        foreach ($branches as $branch) {
            foreach ($branch->products()->get() as $product) {
                if (!in_array($product->id, collect($products)->pluck('id')->toArray(), true)) {
                    array_push($products, $product);
                }
            }
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
        return view('products.create', compact(['companies','iva_taxes','ice_taxes','irbpnr_taxes']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function store(Request $request)
    {
        $input = $request->except(['company', 'branch']);
        $input['branch_id'] = $request->branch;
        $product = Product::create($input);
        $input_product_taxes['product_id']=$product->id;
        $input_product_taxes['iva_tax_id']=$request->iva_tax;
        $input_product_taxes['ice_tax_id']=$request->ice_tax;
        $input_product_taxes['irbpnr_tax_id']=$request->irbpnr_tax;
        $product = ProductTax::create($input_product_taxes);
        return true;
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
    private function update(Request $request, Product $product)
    {
        $product->fill($request->except(['company', 'branch']))->save();
        return true;
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
        return redirect()->route('products.index')->with(['status' => trans_choice(__('message.model_deactivated_successfully', ['model' => trans_choice(__('view.product'), 0)]), 0)]);
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
