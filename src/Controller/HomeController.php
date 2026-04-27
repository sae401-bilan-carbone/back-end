<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'api_home', methods: ['GET'])]
    public function index(): Response
    {
        $html = <<<HTML
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <title>API Documentation</title>
            </head>
            <body>
                <h1>Vesta API Documentation 🍃</h1>
                <p>Base URL : <code>/</code> &mdash; Authentification par JWT (header <code>Authorization: Bearer &lt;token&gt;</code>)</p>
                <p>Le site Vesta utilisant l'API est <a href="https://sae401.mmi24c16.mmi-troyes.fr/fr/" target="_blank">disponible ici</a> (à consulter en vue mobile).</p>
                <p>Github back-end (API) : <a href="https://github.com/sae401-bilan-carbone/front-end" target="_blank">github/sae401-bilan-carbone/back-end</a></p>
                <p>Github front-end (API) : <a href="https://github.com/sae401-bilan-carbone/front-end" target="_blank">github/sae401-bilan-carbone/front-end</a></p>
                <hr>

                <h2><u>Auth</u></h2>

                <h3>POST /register</h3>
                <p>Crée un nouveau compte et retourne un token JWT.</p>
                <p><strong>Body (JSON) :</strong></p>
                <pre>
            {
            "email":    "string (requis)",
            "password": "string (requis)",
            "name":     "string"
            }
                </pre>
                <p><strong>Réponses :</strong></p>
                <ul>
                    <li><code>201</code> — <code>{ message, token, user: { id, email, name } }</code></li>
                    <li><code>400</code> — champs manquants ou invalides</li>
                    <li><code>409</code> — email déjà utilisé</li>
                </ul>

                <h3>POST /login</h3>
                <p>Authentifie un utilisateur et retourne un token JWT via LexikJWTAuthenticationBundle.</p>
                <p><strong>Body (JSON) :</strong></p>
                <pre>
            {
            "email":    "string (requis)",
            "password": "string (requis)"
            }
                </pre>
                <p><strong>Réponses :</strong></p>
                <ul>
                    <li><code>200</code> — <code>{ user: email, id }</code></li>
                    <li><code>401</code> — identifiants incorrects</li>
                </ul>
                <hr>

                <h2><u>Utilisateur courant</u></h2>
                <p><em>Routes protégées — JWT requis.</em></p>

                <h3>GET /me</h3>
                <p>Retourne les informations du compte connecté.</p>
                <p><strong>Réponses :</strong></p>
                <ul>
                    <li><code>200</code> — <code>{ id, email, name, profilePicture, locale }</code></li>
                    <li><code>401</code> — non authentifié</li>
                </ul>

                <h3>PATCH /me</h3>
                <p>Met à jour les informations du compte connecté.</p>
                <p><strong>Body (JSON, tous les champs sont optionnels) :</strong></p>
                <pre>
            {
            "name":           "string",
            "profilePicture": "string",
            "locale":         "string"
            }
                </pre>
                <p><strong>Réponses :</strong></p>
                <ul>
                    <li><code>200</code> — <code>{ message, user: { id, email, name, profilePicture, locale } }</code></li>
                    <li><code>400</code> — JSON invalide ou erreur de validation</li>
                    <li><code>401</code> — non authentifié</li>
                </ul>
                <hr>

                <h2><u>Activités</u></h2>
                <p><em>Routes protégées — JWT requis.</em></p>

                <h3>POST /activities</h3>
                <p>Enregistre une nouvelle activité et calcule son empreinte carbone (CO₂).</p>
                <p><strong>Body (JSON) :</strong></p>
                <pre>
            {
            "type": "string (requis) — ex. \"shopping\", \"food\", \"journey\"",
            "data": "object (requis) — paramètres spécifiques au type d'activité"
            }
                </pre>
                <p><strong>Réponses :</strong></p>
                <ul>
                    <li><code>201</code> — <code>{ status, message, id, calculated_co2, type }</code></li>
                    <li><code>400</code> — champs <code>type</code> ou <code>data</code> manquants</li>
                    <li><code>401</code> — non authentifié</li>
                    <li><code>500</code> — erreur lors du calcul CO₂</li>
                </ul>

                <h3>GET /activities</h3>
                <p>Retourne la liste des activités de l'utilisateur connecté.</p>
                <p><strong>Réponses :</strong></p>
                <ul>
                    <li><code>200</code> — <code>[{ id, type, data, co2, createdAt }]</code></li>
                    <li><code>401</code> — non authentifié</li>
                </ul>

                <h3>DELETE /activities/{id}</h3>
                <ul>
                    <li><code>id</code> (entier, dans l'URL) — identifiant de l'activité à supprimer</li>
                </ul>
                <p><strong>Réponses :</strong></p>
                <ul>
                    <li><code>204</code> — suppression réussie</li>
                    <li><code>401</code> — non authentifié</li>
                    <li><code>403</code> — l'activité n'appartient pas à l'utilisateur</li>
                    <li><code>404</code> — activité introuvable</li>
                </ul>

                <h3>GET /activities/stats</h3>
                <p>Retourne les statistiques CO₂ de l'utilisateur connecté.</p>
                <p><strong>Réponses :</strong></p>
                <ul>
                    <li><code>200</code> —
                        <pre>
            {
            "total_emitted":         "float — total CO₂ de l'utilisateur (kg)",
            "average_total_emitted": "float — moyenne globale tous utilisateurs (kg)",
            "by_category": {
                "shopping": "float",
                "food":     "float",
                "journey":  "float"
            },
            "by_week": [
                {
                "week":        "string — ex. \"2024-W14\"",
                "total_co2":   "float",
                "average_co2": "float|null — moyenne tous utilisateurs pour cette semaine"
                }
            ]
            }
                        </pre>
                    </li>
                    <li><code>401</code> — non authentifié</li>
                </ul>
            </body>
            </html>
            HTML;

        return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html']);
    }
}