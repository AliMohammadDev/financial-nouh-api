<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\User\FundService;
use Illuminate\Http\Request;

class FundController extends Controller
{
  public function __construct(
    private FundService $fundService
  ) {}

  public function index() {}

  public function store() {}


  public function show() {}


  public function update() {}

  public function destroy() {}
}