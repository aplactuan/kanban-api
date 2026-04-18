<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Board\StoreBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use Illuminate\Http\JsonResponse;

class StoreBoardController extends Controller
{
    public function __construct(private BoardRepositoryInterface $boardRepository) {}

    public function __invoke(StoreBoardRequest $request): JsonResponse
    {
        /** @var array{name: string, description?: string|null} $validated */
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $this->authorize('create', Board::class);

        $board = $this->boardRepository->createForUser($user, $validated);

        return (new BoardResource($board))->response()->setStatusCode(201);
    }
}
