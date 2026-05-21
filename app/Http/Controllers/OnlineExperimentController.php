<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use App\Support\OnlineExperimentLab;
use Illuminate\View\View;

class OnlineExperimentController extends Controller
{
    public function hub(): View
    {
        $labs = OnlineExperimentLab::playable();

        return view('frontend.experiments.online-hub', [
            'labs' => $labs,
            'labCount' => count($labs),
        ]);
    }

    public function play(string $labSlug): View
    {
        $lab = OnlineExperimentLab::findBySlug($labSlug);
        abort_unless($lab !== null && OnlineExperimentLab::isPlayable($lab['type'] ?? null), 404);

        $articleUrl = null;
        $articleSlug = $lab['article_slug'] ?? null;
        if ($articleSlug) {
            $article = Experiment::query()->published()->where('slug', $articleSlug)->first();
            if ($article) {
                $articleUrl = route('experiments.show', $article);
            }
        }

        return view('frontend.experiments.online-play', [
            'lab' => $lab,
            'labType' => $lab['type'],
            'labTypeLabel' => OnlineExperimentLab::typeLabel($lab['type']),
            'articleUrl' => $articleUrl,
        ]);
    }
}
