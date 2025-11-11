# Correções de Banco de Dados - Estrutura Real

## Decisão Arquitetural

O banco de dados real (`descubramuriae.sql`) usa:
- `vaga` (singular) - não `vagas`
- `estabelecimento` - não `empresas` (mas `empresas` também existe em create_empresas_table.sql)
- `vaga_curriculum` - não `candidaturas`
- `curriculos` (tabela simplificada) ou `curriculum` (tabela completa)

## Mapeamento de Campos

### Tabela `vaga` (real) vs `vagas` (esperado):
- `vaga_id` → `id`
- `descricao` → `titulo` (primeiros 60 chars)
- `sobreaVaga` → `descricao`
- `cargo_id` → precisa JOIN com `cargo`
- `modalidade` (1=Presencial, 2=Remoto) → precisa converter
- `vinculo` (1=CLT, 2=PJ) → precisa converter
- `dtInicio` → `data_publicacao`
- `dtFim` → `data_limite`
- `statusVaga` (1=Pré, 11=Aberta, 91=Suspensa, 99=Finalizada) → `status`
- `estabelecimento_id` → `empresa_id`

### Tabela `estabelecimento` vs `empresas`:
- `estabelecimento_id` → `id`
- `nome` → `nome_social`
- `endereco` → `endereco`
- `email` → `email`
- Não tem: `cnpj`, `segmento`, `cidade`, `estado`, `telefone`, etc.

### Tabela `vaga_curriculum` vs `candidaturas`:
- `vaga_id` + `curriculum_id` (PK composta) → `id`
- `dateCandidatura` → `data_candidatura`
- Não tem: `status`, `data_avaliacao`, `observacoes`

## Estratégia de Correção

Como a estrutura é muito diferente, temos duas opções:
1. Adaptar todos os arquivos para usar a estrutura real (mais trabalhoso, mas correto)
2. Criar views ou adaptadores (mais rápido, mas menos eficiente)

Vou optar pela opção 1: adaptar todos os arquivos para usar a estrutura real do banco.

