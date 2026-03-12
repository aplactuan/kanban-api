<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardResource;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use Illuminate\Http\Request;

class ShowBoardController extends Controller
{
    public function __construct(private BoardRepositoryInterface $boardRepository) {}

    public function __invoke(Request $request, int $board): BoardResource
    {
        /** @var User $user */
        $user = $request->user();

        return new BoardResource($this->boardRepository->findForUserByIdOrFail($user, $board));
    }
}
