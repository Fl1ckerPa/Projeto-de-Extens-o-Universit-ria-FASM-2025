/**
 * Script para proteger páginas restritas
 * Deve ser incluído em todas as páginas que requerem autenticação
 */

(function() {
    let tentativas = 0;
    const maxTentativas = 50; // 5 segundos máximo
    
    // Aguardar authManager estar disponível
    const verificarAuth = async () => {
        tentativas++;
        
        if (!window.authManager) {
            if (tentativas < maxTentativas) {
                // Aguardar um pouco e tentar novamente
                setTimeout(verificarAuth, 100);
            } else {
                console.error('authManager não foi carregado a tempo');
                // Redirecionar para login
                window.location.href = 'login.html';
            }
            return;
        }
        
        try {
            // Verificar autenticação diretamente
            const user = await window.authManager.checkAuth();
            
            // Verificar se está autenticado
            const isAuthenticated = user && user.autenticado === true;
            
            if (!isAuthenticated) {
                console.log('Usuário não autenticado, redirecionando...');
                // Redirecionar para login com URL de retorno
                const currentUrl = encodeURIComponent(window.location.href);
                window.location.href = `login.html?redirect=${currentUrl}`;
                return;
            }
            
            console.log('Usuário autenticado:', user);
            
            // Se autenticado, atualizar navegação
            await window.authManager.updateNavigation();
            
            // Disparar evento customizado para indicar que autenticação foi verificada
            window.dispatchEvent(new CustomEvent('authVerified', { 
                detail: { authenticated: true, user: user } 
            }));
        } catch (error) {
            console.error('Erro ao verificar autenticação:', error);
            // Em caso de erro, redirecionar para login
            const currentUrl = encodeURIComponent(window.location.href);
            window.location.href = `login.html?redirect=${currentUrl}`;
        }
    };
    
    // Iniciar verificação quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', verificarAuth);
    } else {
        // DOM já está pronto
        verificarAuth();
    }
})();

