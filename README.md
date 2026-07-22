# TPB Business - Sistema de Agendamentos e Gestão Financeira

## 📋 Descrição

TPB Business é um sistema de agendamentos e gestão financeira desenvolvido para pequenas empresas e microempreendedores (MEI). A plataforma permite gerenciar agendamentos de serviços, clientes, e controlar fluxo de caixa com facilidade.

O sistema foi arquitetado como uma **API robusta** em Laravel, com integração de autenticação segura via JWT e Sanctum. Uma interface gráfica moderna será desenvolvida em um projeto separado focado no frontend.

## ✨ Características Principais

- ✅ **Agendamentos de Serviços** - Crie e gerencie agendamentos de forma simples e intuitiva
- ✅ **Registro de Serviços** - Associe serviços aos agendamentos com dados completos
- ✅ **Gestão de Clientes** - Cadastre clientes (pessoa física ou jurídica)
- ✅ **Fluxo de Caixa Inteligente** - Registre receitas e despesas com filtros avançados
- ✅ **Confirmação de Pagamentos** - Confirme pagamentos e registre automaticamente no fluxo
- ✅ **Autenticação Segura** - Sistema de login com JWT e tokens pessoais de acesso
- ✅ **Sessões de Usuário** - Rastreamento de sessões ativas
- ✅ **Suporte a API REST** - Endpoints padronizados para integração com frontend

## 🏗️ Arquitetura do Sistema

### Estrutura Técnica

```
Laravel 10+ | PHP 8.1+
├── API REST com autenticação JWT/Sanctum
├── Database: PostgreSQL
├── ORM: Eloquent
└── Frontend: Em desenvolvimento (projeto separado)
```

### Componentes Principais

#### 1. **Modelos de Dados**

- **User** - Usuários do sistema (donos de negócios)
- **Cliente** - Clientes (pessoa física ou jurídica)
- **Servico** - Tipos/categorias de serviços oferecidos
- **Agendamento** - Agendamentos dos serviços com clientes
- **FluxoCaixa** - Registro de receitas e despesas
- **UserSession** - Controle de sessões ativas

---

## 🎯 Funcionalidades Detalhadas

### 📅 Agendamentos

O sistema permite criar e gerenciar agendamentos de forma flexível:

```
Agendamento
├── Cliente (referência ao cliente)
├── Serviço (serviço a ser prestado)
├── Data/Hora
├── Status (pendente, confirmado, concluído, cancelado)
└── Notas adicionais
```

**Fluxo Típico:**
1. Selecionar ou criar um cliente
2. Escolher o serviço a ser prestado
3. Definir data e hora do agendamento
4. Confirmar o agendamento no sistema
5. Após conclusão, registrar o pagamento

### 👥 Gestão de Clientes

Cadastre clientes com informações completas:

- **Dados Pessoais/Empresariais**
  - Nome completo ou Razão social
  - CPF ou CNPJ
  - Email e telefone
  - Endereço completo

- **Tipos Suportados**
  - Pessoa Física (PF)
  - Pessoa Jurídica (PJ)

### 💰 Fluxo de Caixa

Controle financeiro completo da sua empresa:

#### Receitas
- Registradas automaticamente quando pagamento é confirmado
- Vinculadas ao agendamento e cliente
- Data de recebimento

#### Despesas
- Registre despesas operacionais
- Categorize por tipo
- Acompanhe gastos recorrentes

#### Funcionalidades
- **Filtros Avançados** - Filtre por data, tipo, cliente, serviço, status
- **Relatórios** - Visualize resumos do fluxo de caixa
- **Confirmação de Pagamentos** - Confirme recebimentos e registre automaticamente

**Fluxo de Pagamento:**
```
Agendamento Concluído
    ↓
Registrar Pagamento
    ↓
Confirmar Pagamento
    ↓
Receita registrada em Fluxo de Caixa
    ↓
Relatório atualizado automaticamente
```

---

## 🚀 Como Usar

### 1. **Primeiro Acesso**

```bash
# Clonar o repositório
git clone https://github.com/pachecotatih/tpb-business.git

# Instalar dependências
composer install
npm install

# Configurar arquivo .env
cp .env.example .env
php artisan key:generate

# Executar migrações
php artisan migrate

# Seed com dados de exemplo (opcional)
php artisan db:seed
```

### 2. **Autenticação**

Faça login na plataforma com suas credenciais. O sistema retorna um token JWT para uso na API.

```
POST /api/login
{
  "email": "usuario@example.com",
  "password": "senha"
}
```

### 3. **Criar um Cliente**

```
POST /api/clientes
{
  "nome": "João Silva",
  "cpf": "123.456.789-00",
  "email": "joao@example.com",
  "telefone": "(11) 9999-9999",
  "tipo": "PF"
}
```

### 4. **Cadastrar Serviço**

```
POST /api/servicos
{
  "nome": "Corte de Cabelo",
  "descricao": "Corte de cabelo masculino",
  "valor": 50.00
}
```

### 5. **Criar Agendamento**

```
POST /api/agendamentos
{
  "cliente_id": 1,
  "servico_id": 1,
  "data": "2026-07-15",
  "hora": "14:30",
  "status": "pendente"
}
```

### 6. **Registrar Pagamento e Receita**

```
POST /api/fluxo-caixa
{
  "agendamento_id": 1,
  "tipo": "receita",
  "valor": 50.00,
  "descricao": "Pagamento recebido - Corte de cabelo",
  "data": "2026-07-15",
  "status": "confirmado"
}
```

### 7. **Registrar Despesa**

```
POST /api/fluxo-caixa
{
  "tipo": "despesa",
  "valor": 150.00,
  "descricao": "Compra de produtos para uso no salão",
  "categoria": "insumos",
  "data": "2026-07-01",
  "status": "confirmada"
}
```

### 8. **Filtrar Fluxo de Caixa**

```
GET /api/fluxo-caixa?
    tipo=receita&
    data_inicio=2026-07-01&
    data_fim=2026-07-31&
    status=confirmado&
    categoria=servicos
```

---

## 📊 Casos de Uso

### Cabeleireiro / Salão de Beleza
1. Registre seus serviços (corte, tinta, progressiva, etc.)
2. Clientes fazem agendamentos
3. Confirme quando o cliente pagar
4. Receita é registrada automaticamente
5. Acompanhe ganhos mensais

### Consultor / Freelancer
1. Agende consultorias com clientes
2. Após conclusão, registre o pagamento
3. Acompanhe fluxo de receitas
4. Registre despesas (software, material, etc.)
5. Visualize lucro líquido

### Pequeno Negócio
1. Cadastre clientes PJ e PF
2. Gerencie múltiplos serviços
3. Controle agendamentos
4. Acompanhe fluxo de caixa completo
5. Exporte relatórios financeiros

---

## 🔒 Segurança

- **Autenticação JWT** - Tokens seguros para API
- **Sanctum** - Proteção de endpoints da API
- **Validação de Entrada** - Proteção contra injeção de dados
- **Criptografia** - Senhas criptografadas no banco de dados
- **Controle de Acesso** - Cada usuário acessa apenas seus dados

---

## 🎨 Interface do Usuário

Uma interface gráfica moderna será desenvolvida em um projeto separado com:

- Dashboard intuitivo com resumo do fluxo de caixa
- Cadastro de receitas e despesas no fluxo de caixa
- Cadastro de clientes e serviços
- Calendário visual para agendamentos
- Responsivo para mobile
- Gráficos de receitas e despesas (em breve)
- Relatórios exportáveis (PDF, Excel) (em breve)
- Notificações e lembretes (em breve)

[Repositório da Interface Gráfica](https://github.com/pachecotatih/tpb_business_flutter)

**Projeto Frontend**: *(Link será disponibilizado em breve)*

---

## 📦 Requisitos do Sistema

- PHP 8.1 ou superior
- Composer
- Node.js 16+ (para assets)
- Banco de dados (PostgreSQL ou MySQL)
- Git

---

## 🛠️ Stack Tecnológico

### Backend
- **Laravel 10+** - Framework PHP
- **Eloquent ORM** - Manipulação de dados
- **JWT** - Autenticação de API
- **Sanctum** - Proteção de endpoints
- **PHPUnit** - Testes automatizados

### Frontend (Em Desenvolvimento)
- React / Vue.js / Svelte
- TypeScript
- Tailwind CSS ou similar
- Responsive Design

---

## 📋 Roadmap Futuro

- [ ] Interface gráfica completa
- [ ] Relatórios avançados com gráficos
- [ ] Sistema de notificações por SMS/Email
- [ ] Integração com gateway de pagamento
- [ ] App mobile nativo
- [ ] Backup automático de dados
- [ ] Multi-idioma (português, inglês, espanhol)
- [ ] API de terceiros para CRM
- [ ] Agendamento automático de lembretes

---

## 📞 Suporte

Para dúvidas ou problemas:

1. Consulte a documentação da API
2. Verifique logs em `storage/logs/`
3. Entre em contato: [seu email/contato]

---

## 📝 Licença

Este projeto é proprietário. Todos os direitos reservados.

---

## 👨‍💻 Desenvolvimento

### Executar servidor de desenvolvimento

```bash
php artisan serve
npm run dev
```

### Executar testes

```bash
php artisan test
```

### Seed de dados

```bash
php artisan db:seed
```

---

**TPB Business** - Facilitando a gestão de pequenos negócios 💼
