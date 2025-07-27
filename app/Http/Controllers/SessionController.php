<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionCreateRequest;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SessionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SessionService $sessionService
    ) {}

    /**
     * Display the specified session.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $session = $this->sessionService->getSessionWithDetails($id, $request->user());
            $sessionData = $this->sessionService->formatDetailedSessionResponse($session);

            return response()->json([
                'success' => true,
                'message' => 'Session retrieved successfully',
                'data' => $sessionData,
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
                'error' => 'The requested session does not exist or you do not have permission to access it.',
            ], Response::HTTP_NOT_FOUND);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'You can only access your own sessions.',
            ], Response::HTTP_FORBIDDEN);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve session',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Close the specified session.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function close(Request $request, int $id): JsonResponse
    {
        try {
            $closedSession = $this->sessionService->closeSession($id, $request->user());
            $sessionData = $this->sessionService->formatDetailedSessionResponse($closedSession);

            return response()->json([
                'success' => true,
                'message' => 'Session closed successfully',
                'data' => $sessionData,
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
                'error' => 'The requested session does not exist or you do not have permission to access it.',
            ], Response::HTTP_NOT_FOUND);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'You can only close your own sessions.',
            ], Response::HTTP_FORBIDDEN);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid operation',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close session',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created session.
     *
     * @param SessionCreateRequest $request
     * @return JsonResponse
     */
    public function store(SessionCreateRequest $request): JsonResponse
    {
        try {
            $session = $this->sessionService->createSession($request->user());
            $sessionData = $this->sessionService->formatSessionResponse($session);

            return response()->json([
                'success' => true,
                'message' => 'Session created successfully',
                'data' => $sessionData,
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create session',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
