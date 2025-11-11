/**
 * Módulo de Autenticação
 * Gerencia autenticação, sessão e proteção de páginas
 */

class AuthManager {
    constructor() {
        this.user = null;
        this.isAuthenticated = false;
        this.checkEndpoint = '../PHP/check_user_type.php';
        this.logoutEndpoint = '../PHP/logout.php';
    }

    /**
     * Verifica se o usuário está autenticado
     * @returns {Promise<Object>} Dados do usuário ou null
     */
    async checkAuth() {
        try {
            const response = await fetch(this.checkEndpoint, {
                method: 'GET',
                credentials: 'same-origin', // Incluir cookies da sessão
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Debug
            console.log('Resposta checkAuth:', data);
            
            // Verificar se está autenticado
            if (data.status === 'success' && data.dados && data.dados.autenticado === true) {
                this.user = data.dados;
                this.isAuthenticated = true;
                console.log('Usuário autenticado:', this.user);
                return this.user;
            } else {
                // Usuário não autenticado
                this.user = data.dados || null;
                this.isAuthenticated = false;
                console.log('Usuário não autenticado');
                return this.user; // Retornar dados mesmo se não autenticado (para compatibilidade)
            }
        } catch (error) {
            console.error('Erro ao verificar autenticação:', error);
            this.user = null;
            this.isAuthenticated = false;
            return null;
        }
    }

    /**
     * Protege uma página - redireciona para login se não autenticado
     * @param {boolean} requireAuth - Se true, redireciona se não autenticado
     */
    async protectPage(requireAuth = true) {
        const user = await this.checkAuth();
        
        // Verificar se está autenticado (user pode existir mas autenticado = false)
        const isAuthenticated = user && user.autenticado === true;
        
        if (requireAuth && !isAuthenticated) {
            // Redirecionar para login com URL de retorno
            const currentUrl = encodeURIComponent(window.location.href);
            window.location.href = `login.html?redirect=${currentUrl}`;
            return false;
        }
        
        return isAuthenticated;
    }

    /**
     * Realiza logout
     */
    async logout() {
        try {
            const response = await fetch(this.logoutEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                this.user = null;
                this.isAuthenticated = false;
                window.location.href = 'login.html';
            } else {
                console.error('Erro ao fazer logout:', data);
                // Mesmo assim, limpar dados locais e redirecionar
                this.user = null;
                this.isAuthenticated = false;
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Erro ao fazer logout:', error);
            // Mesmo assim, limpar dados locais e redirecionar
            this.user = null;
            this.isAuthenticated = false;
            window.location.href = 'login.html';
        }
    }

    /**
     * Atualiza a navegação baseada no estado de autenticação
     */
    async updateNavigation() {
        const user = await this.checkAuth();
        
        if (user) {
            this.showAuthenticatedNav();
            this.hidePublicNav();
        } else {
            this.hideAuthenticatedNav();
            this.showPublicNav();
        }
    }

    /**
     * Mostra elementos de navegação para usuários autenticados
     */
    showAuthenticatedNav() {
        // Menu desktop
        const authNavDesktop = document.getElementById('authNavDesktop');
        const publicNavDesktop = document.getElementById('publicNavDesktop');
        const authNavButtons = document.getElementById('authNavButtons');
        const publicNavButtons = document.getElementById('publicNavButtons');
        
        if (authNavDesktop) authNavDesktop.classList.remove('d-none');
        if (publicNavDesktop) publicNavDesktop.classList.add('d-none');
        if (authNavButtons) authNavButtons.classList.remove('d-none');
        if (publicNavButtons) publicNavButtons.classList.add('d-none');

        // Menu mobile
        const authNavMobile = document.getElementById('authNavMobile');
        const publicNavMobile = document.getElementById('publicNavMobile');
        
        if (authNavMobile) authNavMobile.classList.remove('d-none');
        if (publicNavMobile) publicNavMobile.classList.add('d-none');

        // Botões de ação
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutBtnMobile = document.getElementById('logoutBtnMobile');
        const userMenu = document.getElementById('userMenu');
        
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }
        
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }
        
        if (userMenu && this.user) {
            const userName = this.user.user_nome || 'Usuário';
            userMenu.textContent = userName;
        }
        
        // Atualizar mensagem de boas-vindas
        const welcomeUserName = document.getElementById('welcomeUserName');
        const publicWelcome = document.getElementById('publicWelcome');
        const authWelcome = document.getElementById('authWelcome');
        
        if (this.user && welcomeUserName) {
            const userName = this.user.user_nome || 'Usuário';
            welcomeUserName.textContent = `Bem Vindo ${userName}!`;
        }
        
        if (publicWelcome) publicWelcome.classList.add('d-none');
        if (authWelcome) authWelcome.classList.remove('d-none');
    }

    /**
     * Esconde elementos de navegação para usuários autenticados
     */
    hideAuthenticatedNav() {
        const authNavDesktop = document.getElementById('authNavDesktop');
        const authNavMobile = document.getElementById('authNavMobile');
        const authNavButtons = document.getElementById('authNavButtons');
        
        if (authNavDesktop) authNavDesktop.classList.add('d-none');
        if (authNavMobile) authNavMobile.classList.add('d-none');
        if (authNavButtons) authNavButtons.classList.add('d-none');
    }

    /**
     * Mostra elementos de navegação para usuários não autenticados
     */
    showPublicNav() {
        const publicNavDesktop = document.getElementById('publicNavDesktop');
        const publicNavMobile = document.getElementById('publicNavMobile');
        const publicNavButtons = document.getElementById('publicNavButtons');
        
        if (publicNavDesktop) publicNavDesktop.classList.remove('d-none');
        if (publicNavMobile) publicNavMobile.classList.remove('d-none');
        if (publicNavButtons) publicNavButtons.classList.remove('d-none');
    }

    /**
     * Esconde elementos de navegação para usuários não autenticados
     */
    hidePublicNav() {
        const publicNavDesktop = document.getElementById('publicNavDesktop');
        const publicNavMobile = document.getElementById('publicNavMobile');
        const publicNavButtons = document.getElementById('publicNavButtons');
        
        if (publicNavDesktop) publicNavDesktop.classList.add('d-none');
        if (publicNavMobile) publicNavMobile.classList.add('d-none');
        if (publicNavButtons) publicNavButtons.classList.add('d-none');
        
        // Mostrar conteúdo público
        const publicWelcome = document.getElementById('publicWelcome');
        const authWelcome = document.getElementById('authWelcome');
        
        if (publicWelcome) publicWelcome.classList.remove('d-none');
        if (authWelcome) authWelcome.classList.add('d-none');
    }

    /**
     * Oculta conteúdo restrito quando usuário não está autenticado
     */
    hideRestrictedContent() {
        const restrictedElements = document.querySelectorAll('[data-restricted]');
        restrictedElements.forEach(el => {
            if (!this.isAuthenticated) {
                el.style.display = 'none';
            }
        });
    }

    /**
     * Mostra conteúdo restrito quando usuário está autenticado
     */
    showRestrictedContent() {
        const restrictedElements = document.querySelectorAll('[data-restricted]');
        restrictedElements.forEach(el => {
            if (this.isAuthenticated) {
                el.style.display = '';
            }
        });
    }
}

// Criar instância global
const authManager = new AuthManager();

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', async () => {
    // Verificar autenticação
    await authManager.checkAuth();
    
    // Atualizar navegação
    await authManager.updateNavigation();
    
    // Esconder/mostrar conteúdo restrito
    if (authManager.isAuthenticated) {
        authManager.showRestrictedContent();
    } else {
        authManager.hideRestrictedContent();
    }
});

// Exportar para uso global
window.authManager = authManager;

