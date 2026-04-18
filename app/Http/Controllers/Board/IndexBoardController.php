<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Concerns\ParsesIncludes;
use App\Http\Controllers\Controller;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexBoardController extends Controller
{
    use ParsesIncludes;

    public function __construct(private BoardRepositoryInterface $boardRepository) {}

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $this->authorize('viewAny', Board::class);

        $boards = $this->boardRepository->getAllForUser($user);

        $relations = $this->parseIncludes($request, ['columns', 'columns.tasks']);

        if ($relations !== []) {
            $boards->load($relations);
        }

        return BoardResource::collection($boards);
    }
}
