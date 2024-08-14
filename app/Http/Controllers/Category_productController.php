<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategory_productRequest;
use App\Http\Requests\UpdateCategory_productRequest;
use App\Repositories\Category_productRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Response;

class Category_productController extends AppBaseController
{
    /** @var Category_productRepository $categoryProductRepository*/
    private $categoryProductRepository;

    public function __construct(Category_productRepository $categoryProductRepo)
    {
        $this->categoryProductRepository = $categoryProductRepo;
    }

    /**
     * Display a listing of the Category_product.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        // $categoryProducts = $this->categoryProductRepository->all();
        $categoryProducts = \App\Models\Category_product::all();

        return view('category_products.index')
            ->with('categoryProducts', $categoryProducts);
    }

    /**
     * Show the form for creating a new Category_product.
     *
     * @return Response
     */
    public function create()
    {
        $concessions = \App\Models\Concession::all();
        return view('category_products.create')->with('concessions', $concessions);
    }

    /**
     * Store a newly created Category_product in storage.
     *
     * @param CreateCategory_productRequest $request
     *
     * @return Response
     */
    public function store(CreateCategory_productRequest $request)
    {
        $input = $request->all();

        // $categoryProduct = $this->categoryProductRepository->create($input);
        $categoryProduct = \App\Models\Category_product::create([
            'name' => $input['name'],
            'id_concession' => 1
        ]);

        Flash::success('Category Product saved successfully.');

        return redirect(route('categoryProducts.index'));
    }

    /**
     * Display the specified Category_product.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $categoryProduct = $this->categoryProductRepository->find($id);

        if (empty($categoryProduct)) {
            Flash::error('Category Product not found');

            return redirect(route('categoryProducts.index'));
        }

        return view('category_products.show')->with('categoryProduct', $categoryProduct);
    }

    /**
     * Show the form for editing the specified Category_product.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $categoryProduct = $this->categoryProductRepository->find($id);
        // $concessions = \App\Models\Concession::all();

        if (empty($categoryProduct)) {
            Flash::error('Category Product not found');

            return redirect(route('categoryProducts.index'));
        }

        return view('category_products.edit')->with('categoryProduct', $categoryProduct);
    }

    /**
     * Update the specified Category_product in storage.
     *
     * @param int $id
     * @param UpdateCategory_productRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCategory_productRequest $request)
    {
        $input = $request->all();
        $categoryProduct = $this->categoryProductRepository->find($id);

        if (empty($categoryProduct)) {
            Flash::error('Category Product not found');

            return redirect(route('categoryProducts.index'));
        }

        // $categoryProduct = $this->categoryProductRepository->update($request->all(), $id);
        $categoryProduct = \App\Models\Category_product::create([
            'name' => $input['name'],
            'id_concession' => 1
        ]);

        Flash::success('Category Product updated successfully.');

        return redirect(route('categoryProducts.index'));
    }

    /**
     * Remove the specified Category_product from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $categoryProduct = $this->categoryProductRepository->find($id);

        if (empty($categoryProduct)) {
            Flash::error('Category Product not found');

            return redirect(route('categoryProducts.index'));
        }

        $this->categoryProductRepository->delete($id);

        Flash::success('Category Product deleted successfully.');

        return redirect(route('categoryProducts.index'));
    }
}
