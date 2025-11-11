/**
 * Script para adicionar navegação autenticada em páginas restritas
 * Deve ser incluído após auth.js e antes de protect-page.js
 */

function setupRestrictedNavigation() {
    // Esta função será chamada após a autenticação ser verificada
    // e atualizará a navegação com o nome do usuário
    if (window.authManager && window.authManager.user) {
        const userMenu = document.getElementById('userMenu');
        if (userMenu && window.authManager.user.user_nome) {
            userMenu.textContent = window.authManager.user.user_nome;
        }
        
        // Configurar botões de logout
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutBtnMobile = document.getElementById('logoutBtnMobile');
        
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.authManager.logout();
            });
        }
        
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener('click', (e) => {
                e.preventDefault();
                window.authManager.logout();
            });
        }
    }
}

// Aguardar DOM e authManager
document.addEventListener('DOMContentLoaded', () => {
    if (window.authManager) {
        window.authManager.checkAuth().then(() => {
            setupRestrictedNavigation();
        });
    } else {
        // Aguardar authManager ser carregado
        const checkAuth = setInterval(() => {
            if (window.authManager) {
                clearInterval(checkAuth);
                window.authManager.checkAuth().then(() => {
                    setupRestrictedNavigation();
                });
            }
        }, 100);
    }
});

