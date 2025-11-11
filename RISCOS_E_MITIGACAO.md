# 🚨 Riscos e Mitigação - Projeto Conectando Talentos

**Data:** 2025-01-XX  
**Versão:** 3.0

---

## 📊 Os 3 Maiores Riscos Atuais

### 🔴 RISCO 1: Falta de Proteção CSRF (Cross-Site Request Forgery)

**Criticidade:** 🔴 ALTA  
**Impacto:** Médio a Alto  
**Probabilidade:** Média

#### Descrição
O sistema não possui proteção contra ataques CSRF, permitindo que requisições maliciosas sejam enviadas em nome de usuários autenticados.

#### Cenário de Ataque
1. Usuário faz login em `http://localhost:8000`
2. Em outra aba, acessa site malicioso
3. Site malicioso envia requisição POST para `http://localhost:8000/PHP/gestao_vagas_empresa.php?acao=excluir_vaga&id=1`
4. Se a sessão do usuário ainda estiver ativa, a vaga é excluída

#### Plano de Mitigação

**Implementação de Tokens CSRF:**

1. **Criar classe CSRF em `lib/CSRF.php`:**
```php
<?php
class CSRF {
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        return $token;
    }

    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Token expira em 1 hora
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function getToken() {
        if (!isset($_SESSION['csrf_token'])) {
            return self::generateToken();
        }
        return $_SESSION['csrf_token'];
    }
}
```

2. **Incluir em `lib/bootstrap.php`:**
```php
require_once __DIR__ . '/CSRF.php';
```

3. **Adicionar token em formulários HTML:**
```html
<input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">
```

4. **Validar em endpoints POST:**
```php
// No início de cada endpoint POST
if (!CSRF::validateToken(Request::post('csrf_token'))) {
    Response::error('Token CSRF inválido ou expirado', null, 403);
}
```

**Prioridade de Implementação:** 🔴 ALTA  
**Esforço Estimado:** 4-6 horas  
**Arquivos Afetados:**
- `lib/CSRF.php` (novo)
- `lib/bootstrap.php` (modificar)
- Todos os arquivos HTML com formulários
- Todos os endpoints PHP que recebem POST

---

### 🔴 RISCO 2: Sessão PHP Não Expira Automaticamente

**Criticidade:** 🔴 ALTA  
**Impacto:** Médio  
**Probabilidade:** Alta

#### Descrição
As sessões PHP não possuem timeout configurado, permitindo que sessões permaneçam ativas indefinidamente, aumentando o risco de uso indevido se o dispositivo for comprometido.

#### Cenário de Risco
1. Usuário faz login em computador público
2. Esquece de fazer logout
3. Outra pessoa acessa o navegador e pode usar a sessão ativa
4. Sessão permanece válida por dias ou até servidor reiniciar

#### Plano de Mitigação

**Implementação de Timeout de Sessão:**

1. **Modificar `lib/Session.php` para incluir verificação de expiração:**
```php
public static function start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configurar timeout de sessão (1 hora)
        ini_set('session.gc_maxlifetime', 3600);
        session_set_cookie_params(3600); // Cookie expira em 1 hora
        
        session_start();
        
        // Verificar última atividade
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > 3600) {
                // Sessão expirada
                session_destroy();
                session_start();
            }
        }
        
        $_SESSION['last_activity'] = time();
    }
}
```

2. **Adicionar verificação em `lib/auth.php`:**
```php
function verificarAutenticacao() {
    Session::start(); // Garantir que sessão está iniciada
    
    if (!Session::get('user_id')) {
        if (Request::isPost() || strpos($_SERVER['REQUEST_URI'], '.php') !== false) {
            Response::error('Acesso não autorizado. Faça login para continuar.', null, 401);
        } else {
            header('Location: ../HTML/login.html');
            exit;
        }
    }
    
    // Verificar expiração de sessão
    $lastActivity = Session::get('last_activity');
    if ($lastActivity && (time() - $lastActivity > 3600)) {
        Session::destroy('user_id');
        Session::destroy('user_type');
        Session::destroy('user_nome');
        Session::destroy('user_email');
        Session::destroy('last_activity');
        
        if (Request::isPost()) {
            Response::error('Sessão expirada. Faça login novamente.', null, 401);
        } else {
            header('Location: ../HTML/login.html?expired=1');
            exit;
        }
    }
    
    // Atualizar última atividade
    Session::set('last_activity', time());
}
```

3. **Adicionar refresh automático em páginas HTML (JavaScript):**
```javascript
// Atualizar última atividade a cada 5 minutos
setInterval(function() {
    fetch('../PHP/session_refresh.php', { method: 'POST' });
}, 5 * 60 * 1000);
```

**Prioridade de Implementação:** 🔴 ALTA  
**Esforço Estimado:** 2-3 horas  
**Arquivos Afetados:**
- `lib/Session.php` (modificar)
- `lib/auth.php` (modificar)
- `PHP/session_refresh.php` (novo - opcional)

---

### 🟡 RISCO 3: Falta de Rate Limiting em Endpoints Sensíveis

**Criticidade:** 🟡 MÉDIA  
**Impacto:** Baixo a Médio  
**Probabilidade:** Média

#### Descrição
Endpoints de login, cadastro e envio de candidaturas não possuem limitação de taxa, permitindo ataques de força bruta e abuso do sistema.

#### Cenário de Risco
1. Atacante tenta milhares de logins com diferentes senhas
2. Sistema pode ser sobrecarregado
3. Possível descobrir senhas válidas por força bruta
4. Envio massivo de candidaturas pode comprometer performance

#### Plano de Mitigação

**Implementação de Rate Limiting:**

1. **Criar classe RateLimiter em `lib/RateLimiter.php`:**
```php
<?php
class RateLimiter {
    private static $cacheDir = __DIR__ . '/../cache/rate_limit/';
    
    public static function check($key, $maxAttempts = 5, $windowSeconds = 3600) {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
        
        $file = self::$cacheDir . md5($key) . '.json';
        
        if (!file_exists($file)) {
            $data = ['attempts' => 1, 'first_attempt' => time()];
            file_put_contents($file, json_encode($data));
            return true;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        // Resetar se window expirou
        if (time() - $data['first_attempt'] > $windowSeconds) {
            $data = ['attempts' => 1, 'first_attempt' => time()];
            file_put_contents($file, json_encode($data));
            return true;
        }
        
        // Verificar limite
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        
        // Incrementar tentativas
        $data['attempts']++;
        file_put_contents($file, json_encode($data));
        return true;
    }
    
    public static function getRemainingTime($key, $windowSeconds = 3600) {
        $file = self::$cacheDir . md5($key) . '.json';
        if (!file_exists($file)) {
            return 0;
        }
        
        $data = json_decode(file_get_contents($file), true);
        $elapsed = time() - $data['first_attempt'];
        return max(0, $windowSeconds - $elapsed);
    }
}
```

2. **Aplicar em `PHP/login.php`:**
```php
// No início do arquivo, após validações básicas
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$loginKey = "login_" . $ipAddress . "_" . $identificador;

if (!RateLimiter::check($loginKey, 5, 3600)) {
    $remaining = RateLimiter::getRemainingTime($loginKey, 3600);
    Response::error("Muitas tentativas de login. Tente novamente em " . ceil($remaining / 60) . " minutos.", null, 429);
}
```

3. **Aplicar em `PHP/candidaturas.php` (envio):**
```php
// No case 'enviar'
$userId = getUserId();
$candidaturaKey = "candidatura_" . $userId;

if (!RateLimiter::check($candidaturaKey, 10, 3600)) {
    Response::error("Limite de candidaturas por hora atingido. Tente novamente mais tarde.", null, 429);
}
```

4. **Adicionar em `lib/bootstrap.php`:**
```php
require_once __DIR__ . '/RateLimiter.php';
```

**Prioridade de Implementação:** 🟡 MÉDIA  
**Esforço Estimado:** 3-4 horas  
**Arquivos Afetados:**
- `lib/RateLimiter.php` (novo)
- `PHP/login.php` (modificar)
- `PHP/cadastro.php` (modificar)
- `PHP/candidaturas.php` (modificar)
- Criar diretório `cache/rate_limit/`

---

## 📋 Matriz Completa de Riscos

| # | Risco | Criticidade | Impacto | Probabilidade | Status | Mitigação |
|---|-------|-------------|---------|---------------|--------|-----------|
| 1 | CSRF Protection | 🔴 Alta | Médio | Média | ❌ Não implementado | Tokens CSRF |
| 2 | Sessão não expira | 🔴 Alta | Médio | Alta | ❌ Não implementado | Timeout de sessão |
| 3 | Rate Limiting | 🟡 Média | Baixo | Média | ❌ Não implementado | RateLimiter |
| 4 | SQL Injection | ✅ Mitigado | Alto | Baixa | ✅ Implementado | Prepared statements |
| 5 | Upload malicioso | ⚠️ Revisar | Alto | Média | ⚠️ Parcial | Validação MIME + extensão |
| 6 | Senhas em texto plano | ✅ Mitigado | Crítico | Baixa | ✅ Implementado | password_hash |
| 7 | Logs de erro expostos | 🟡 Média | Médio | Baixa | ⚠️ Verificar | display_errors=off |
| 8 | Headers de segurança | 🟢 Baixa | Baixo | Baixa | ❌ Não implementado | X-Frame-Options, CSP |

---

## 🎯 Plano de Ação Prioritário

### Semana 1 (Crítico)
1. ✅ Implementar proteção CSRF
2. ✅ Implementar timeout de sessão
3. ✅ Revisar validação de uploads

### Semana 2 (Importante)
4. ✅ Implementar rate limiting
5. ✅ Configurar headers de segurança
6. ✅ Desabilitar exibição de erros em produção

### Semana 3 (Melhorias)
7. ✅ Implementar logging de segurança
8. ✅ Adicionar monitoramento de tentativas de login
9. ✅ Implementar 2FA (opcional)

---

## 📝 Checklist de Mitigação

- [ ] **CSRF Protection:**
  - [ ] Criar `lib/CSRF.php`
  - [ ] Adicionar tokens em formulários HTML
  - [ ] Validar tokens em endpoints POST
  - [ ] Testar proteção

- [ ] **Timeout de Sessão:**
  - [ ] Modificar `lib/Session.php`
  - [ ] Adicionar verificação em `lib/auth.php`
  - [ ] Testar expiração de sessão
  - [ ] Adicionar mensagem de sessão expirada

- [ ] **Rate Limiting:**
  - [ ] Criar `lib/RateLimiter.php`
  - [ ] Aplicar em login
  - [ ] Aplicar em cadastro
  - [ ] Aplicar em candidaturas
  - [ ] Testar limites

- [ ] **Headers de Segurança:**
  - [ ] Configurar `X-Frame-Options: DENY`
  - [ ] Configurar `X-Content-Type-Options: nosniff`
  - [ ] Configurar `Content-Security-Policy` (básico)
  - [ ] Testar em produção

- [ ] **Configuração de Produção:**
  - [ ] `display_errors = Off`
  - [ ] `log_errors = On`
  - [ ] Configurar `error_log`
  - [ ] Testar logs

---

**Documento gerado em:** 2025-01-XX  
**Próxima revisão:** Após implementação das mitigações

