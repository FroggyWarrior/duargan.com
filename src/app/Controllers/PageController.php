<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\SongModel;

class PageController extends BaseController {
    
    public function index() {
        $db = \App\Core\Database::getInstance()->getConnection();
        $ann_stmt = $db->query("SELECT title, background_color, text FROM announcement WHERE id = 1 AND is_active = 1 LIMIT 1");
        
        // Added the 'new' keyword here!
        $songModel = new \App\Models\SongModel();
        
        // 1. Get the official releases
        $officialReleases = $songModel->getOfficialReleases();
        
        // 2. Define the latest release (exactly as it was in your old index.php)
        $latestRelease = !empty($officialReleases) ? $officialReleases[0] : null;

        // 3. Pass ALL variables down to the view
        $this->render('home', [
            'pageTitle' => "Duargan - Music Producer",
            'currentPage' => 'index',
            'announcement' => $ann_stmt->fetch(),
            'officialReleases' => $officialReleases,
            'latestRelease' => $latestRelease
        ]);
    }

    public function song() {
        $songId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $songModel = new \App\Models\SongModel();
        $song = $songModel->getSongById($songId);

        if (!$song) {
            header('Location: /music');
            exit;
        }

        // Other song with all its data
        $otherSongs = $songModel->getOtherSongsDetailed($songId, 4);

        // Map variable names for the View
        $song_genres   = $song['genres'] ?? [];
        $song_platforms = $song['platforms'] ?? [];

        // Generate share buttons
        $shareButtons = $this->generateShareButtons($song);

        $this->render('song', [
            'pageTitle'   => $song['title'] . " | Duargan",
            'currentPage' => 'music',
            'song'        => $song,
            'song_genres' => $song_genres,
            'song_platforms' => $song_platforms,
            'otherSongs'  => $otherSongs,
            'shareButtons'=> $shareButtons
        ]);
    }

    private function generateShareButtons($song) {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $song_url = $base_url . "/song.php?id=" . $song['id']; // Ajusta si cambiaste la ruta
        $encoded_url = urlencode($song_url);
        $encoded_title = urlencode("Listen to \"" . $song['title'] . "\" by Duargan");
        $encoded_text = urlencode("Check out \"" . $song['title'] . "\" by Duargan");

        $share_links = [
            'x' => [
                'url' => "https://twitter.com/intent/tweet?text=" . $encoded_text . "&url=" . $encoded_url,
                'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
                'name' => 'Share on X',
                'color' => '#000000'
            ],
            'facebook' => [
                'url' => "https://www.facebook.com/sharer/sharer.php?u=" . $encoded_url,
                'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
                'name' => 'Share on Facebook',
                'color' => '#1877F2'
            ],
            'email' => [
                'url' => "mailto:?subject=" . $encoded_title . "&body=" . $encoded_text . "%0A%0A" . $encoded_url,
                'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
                'name' => 'Share via Email',
                'color' => '#EA4335'
            ],
            'copy-link' => [
                'url' => '#',
                'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>',
                'name' => 'Copy Link',
                'color' => '#6750A4'
            ]
        ];

        $html = '
        <div class="share-buttons">
            <span>Share this track:</span>
            <div class="share-icons-container">';

        foreach ($share_links as $platform => $data) {
            if ($platform === 'copy-link') {
                $html .= '
                <button type="button" class="share-icon copy-link" data-url="' . $song_url . '" aria-label="' . $data['name'] . '" title="' . $data['name'] . '">
                    ' . $data['icon'] . '
                    <span class="copy-tooltip">Copy link</span>
                </button>';
            } else {
                $html .= '
                <a href="' . $data['url'] . '" class="share-icon ' . $platform . '" target="_blank" rel="noopener" aria-label="' . $data['name'] . '" title="' . $data['name'] . '">
                    ' . $data['icon'] . '
                </a>';
            }
        }

        $html .= '
            </div>
        </div>';

        return $html;
    }

    public function music() {
        $songModel = new \App\Models\SongModel();

        $this->render('music', [
            'pageTitle' => "All My Music | Duargan",
            'currentPage' => 'music',
            'all_genres' => $songModel->getAllGenres(),
            'all_types' => $songModel->getAllTypes(),
            'musicTracks' => $songModel->getAllMusic()
        ]);
    }

    public function about() {
        $this->render('about', [
            'pageTitle' => "About Me | Duargan",
            'currentPage' => 'about'
        ]);
    }

    public function contact() {
        $this->render('contact', [
            'pageTitle' => "Contact Duargan",
            'currentPage' => 'contact'
        ], false);
    }
}