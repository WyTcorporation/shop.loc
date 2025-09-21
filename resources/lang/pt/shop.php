<?php

return [
    'admin' => [
        'brand' => 'Administração da Loja',
        'navigation' => [
            'catalog' => 'Catálogo',
            'sales' => 'Vendas',
            'inventory' => 'Inventário',
            'marketing' => 'Marketing',
            'content' => 'Conteúdo',
            'customers' => 'Clientes',
            'settings' => 'Configurações',
        ],
        'language_switcher' => [
            'label' => 'Idioma da interface',
            'help' => 'Altera o idioma do painel após recarregar a página.',
        ],
        'dashboard' => [
            'filters' => [
                'period' => 'Período',
                'today' => 'Hoje',
                'seven_days' => 'Últimos 7 dias',
                'thirty_days' => 'Últimos 30 dias',
                'ninety_days' => 'Últimos 90 dias',
            ],
            'sales' => [
                'title' => 'Desempenho de vendas',
                'revenue' => 'Receita',
                'orders' => 'Pedidos',
                'average_order_value' => 'Ticket médio',
            ],
            'conversion' => [
                'title' => 'Conversão do checkout',
                'rate' => 'Taxa de conversão',
                'rate_help' => 'Pedidos versus carrinhos no período selecionado.',
                'orders' => 'Pedidos',
                'carts' => 'Carrinhos',
            ],
            'traffic' => [
                'title' => 'Fontes de tráfego',
                'revenue' => 'Participação na receita',
            ],
            'top_products' => [
                'title' => 'Produtos mais vendidos',
                'columns' => [
                    'product' => 'Produto',
                    'sku' => 'SKU',
                    'quantity' => 'Unidades vendidas',
                    'revenue' => 'Receita',
                ],
            ],
            'inventory' => [
                'title' => 'Status do estoque',
                'skus' => 'SKUs monitorados',
                'available_units' => 'Unidades disponíveis',
                'low_stock' => 'Estoque baixo (≤ :threshold)',
            ],
        ],
        'resources' => [
            'products' => [
                'label' => 'Produto',
                'plural_label' => 'Produtos',
            ],
            'categories' => [
                'label' => 'Categoria',
                'plural_label' => 'Categorias',
            ],
            'orders' => [
                'label' => 'Pedido',
                'plural_label' => 'Pedidos',
            ],
            'vendors' => [
                'label' => 'Fornecedor',
                'plural_label' => 'Fornecedores',
            ],
            'inventory' => [
                'label' => 'Item de inventário',
                'plural_label' => 'Inventário',
            ],
            'coupons' => [
                'label' => 'Cupom',
                'plural_label' => 'Cupons',
            ],
            'reviews' => [
                'label' => 'Avaliação',
                'plural_label' => 'Avaliações',
            ],
            'users' => [
                'label' => 'Cliente',
                'plural_label' => 'Clientes',
            ],
            'warehouses' => [
                'label' => 'Armazém',
                'plural_label' => 'Armazéns',
            ],
            'currencies' => [
                'label' => 'Moeda',
                'plural_label' => 'Moedas',
            ],
        ],
    ],

    'navigation' => [
        'catalog' => 'Catálogo',
        'cart' => 'Carrinho',
        'order' => 'Pedido',
    ],

    'meta' => [
        'brand' => 'Shop',
    ],

    'languages' => [
        'uk' => 'Ucraniano',
        'en' => 'Inglês',
        'ru' => 'Russo',
        'pt' => 'Português',
    ],

    'conversation' => [
        'heading' => 'Conversa',
        'system' => 'Sistema',
        'empty' => 'Ainda não há mensagens.',
        'new' => 'Nova mensagem',
        'send' => 'Enviar',
        'sent' => 'Mensagem enviada',
        'message' => 'Mensagem',
    ],

    'common' => [
        'owner' => 'Proprietário',
        'email' => 'E-mail',
        'phone' => 'Telefone',
        'footer_note' => 'Se tiver alguma dúvida, basta responder a este e-mail.',
        'order_title' => 'Pedido nº :number',
        'updates_email' => 'Enviaremos atualizações para :email.',
        'order_number' => 'Número do pedido',
        'items_total' => 'Total de itens',
        'coupon' => 'Cupom',
        'discount' => 'Desconto',
        'used_points' => 'Pontos utilizados',
        'order_total' => 'Total do pedido',
        'total_due' => 'Total a pagar',
        'amount_due' => 'Valor a pagar',
        'status' => 'Status',
        'shipped' => 'Enviado',
        'delivered' => 'Entregue',
        'paid' => 'Pago',
        'shipped_at' => 'Data de envio',
        'delivered_at' => 'Data de entrega',
        'paid_at' => 'Data de pagamento',
        'product' => 'Produto',
        'quantity' => 'Qtd.',
        'price' => 'Preço',
        'sum' => 'Total',
        'items_subtotal' => 'Subtotal dos itens',
        'name' => 'Nome',
        'city' => 'Cidade',
        'address' => 'Endereço',
        'postal_code' => 'CEP',
        'note' => 'Observação',
        'total' => 'Total',
        'tracking_number' => 'Código de rastreamento',
        'created' => 'Criado',
        'updated' => 'Atualizado',
    ],

    'auth' => [
        'greeting' => 'Olá, :name!',
        'reset_link_hint' => 'Um link de redefinição será enviado se a conta existir.',
        'reset' => [
            'subject' => 'Redefinição de senha para :app',
            'heading' => 'Recupere o acesso a :app',
            'intro' => 'Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta em :app.',
            'title' => 'Redefinir senha',
            'button' => 'Redefinir senha',
            'link_help' => 'O botão não funciona? Copie e cole este link no seu navegador:',
            'ignore' => 'Se você não solicitou a redefinição de senha, ignore este e-mail.',
            'changed_subject' => 'Senha de :app alterada',
            'changed_title' => 'Senha alterada',
            'changed_intro' => 'Acabamos de atualizar a senha da sua conta em :app.',
            'changed_warning' => 'Se você não alterou a senha, entre em contato com o suporte ou redefina-a novamente imediatamente para proteger sua conta.',
            'signature' => 'Atenciosamente, equipe :app.',
        ],
        'welcome' => [
            'subject' => 'Bem-vindo(a) ao :app!',
            'title' => 'Bem-vindo(a) ao :app',
            'intro' => 'Obrigado por se registrar no :app. Para concluir, confirme seu endereço de e-mail.',
            'button' => 'Confirmar endereço de e-mail',
            'ignore' => 'Se você não criou a conta, ignore este e-mail.',
        ],
        'verify' => [
            'subject' => 'Confirme o e-mail para :app',
            'title' => 'Confirmar endereço de e-mail',
            'intro' => 'Para ativar sua conta no :app, confirme o endereço de e-mail na próxima hora.',
            'button' => 'Confirmar endereço de e-mail',
            'ignore' => 'Se você não criou a conta, ignore este e-mail.',
        ],
    ],

    'orders' => [
        'placed' => [
            'subject' => 'Obrigado pelo pedido!',
            'subject_line' => 'Seu pedido nº :number foi recebido',
            'intro' => 'O pedido nº :number foi realizado.',
        ],
        'paid' => [
            'subject' => 'Pagamento recebido',
            'subject_line' => 'Pedido nº :number pago',
            'intro' => 'O pedido nº :number foi pago com sucesso.',
            'next' => 'Estamos preparando o envio e avisaremos sobre os próximos passos.',
            'button' => 'Voltar à loja',
        ],
        'shipped' => [
            'subject' => 'Pedido a caminho',
            'subject_line' => 'Pedido nº :number enviado',
            'intro' => 'Encaminhamos o pedido nº :number para a transportadora.',
            'next' => 'Avisaremos assim que chegar.',
            'button' => 'Acompanhar pedido',
        ],
        'delivered' => [
            'subject' => 'Pedido entregue',
            'subject_line' => 'Pedido nº :number entregue',
            'intro' => 'O pedido nº :number foi entregue com sucesso.',
            'thanks' => 'Esperamos que tenha gostado da compra. Obrigado por escolher o :app!',
            'button' => 'Ver pedido',
        ],
        'status_updated' => [
            'subject_line' => 'Status do pedido nº :number atualizado',
        ],
        'sections' => [
            'general' => 'Geral',
            'shipping' => 'Envio',
            'shipment' => 'Remessa',
            'summary' => 'Resumo',
        ],
        'fieldsets' => [
            'shipping_address' => 'Endereço de entrega',
            'billing_address' => 'Endereço de cobrança',
        ],
        'fields' => [
            'user' => 'Usuário',
            'number' => 'Número',
            'total' => 'Total',
            'shipment_status' => 'Status da remessa',
        ],
        'helpers' => [
            'email_auto' => 'Se um usuário for selecionado, o e-mail será preenchido automaticamente.',
        ],
        'hints' => [
            'number_generated' => 'Gerado automaticamente',
        ],
        'actions' => [
            'messages' => 'Mensagens',
            'mark_paid' => 'Marcar como pago',
            'mark_shipped' => 'Marcar como enviado',
            'cancel' => 'Cancelar',
            'resend_confirmation' => 'Reenviar confirmação',
        ],
        'notifications' => [
            'marked_paid' => 'Pedido marcado como pago',
            'marked_shipped' => 'Pedido marcado como enviado',
            'cancelled' => 'Pedido cancelado',
            'confirmation_resent' => 'E-mail de confirmação reenviado',
        ],
        'summary' => [
            'positions' => 'Itens',
            'subtotal' => 'Subtotal',
            'total_order' => 'Total (pedido)',
        ],
        'items' => [
            'title' => 'Produtos no pedido',
            'fields' => [
                'product' => 'Produto',
                'qty' => 'Qtd.',
                'price' => 'Preço',
                'subtotal' => 'Subtotal',
            ],
            'empty_state' => [
                'heading' => 'Ainda não há produtos',
            ],
        ],
        'logs' => [
            'title' => 'Histórico de status',
            'fields' => [
                'from' => 'Anterior',
                'to' => 'Atual',
                'by' => 'Alterado por',
                'note' => 'Observação',
                'deleted_at' => 'Excluído em',
                'created_at' => 'Criado em',
                'updated_at' => 'Atualizado em',
            ],
            'empty_state' => [
                'heading' => 'O status ainda não foi alterado',
            ],
        ],
        'shipment_status' => [
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado',
        ],
        'statuses' => [
            'new' => 'novo',
            'paid' => 'pago',
            'shipped' => 'enviado',
            'cancelled' => 'cancelado',
        ],
        'errors' => [
            'only_new_can_be_marked_paid' => 'Apenas pedidos com o status ":required" podem ser marcados como pagos. O pedido nº:number está atualmente com o status ":status".',
            'only_paid_can_be_marked_shipped' => 'Apenas pedidos com o status ":required" podem ser marcados como enviados. O pedido nº:number está atualmente com o status ":status".',
            'only_new_or_paid_can_be_cancelled' => 'Apenas pedidos com os seguintes status podem ser cancelados: :allowed. O pedido nº:number está atualmente com o status ":status".',
        ],
    ],

    'inventory' => [
        'not_enough_stock' => 'Estoque insuficiente para o produto nº :product_id no depósito nº :warehouse_id.',
        'not_enough_reserved_stock' => 'Estoque reservado insuficiente para o produto nº :product_id no depósito nº :warehouse_id.',
        'fields' => [
            'product' => 'Produto',
            'warehouse' => 'Armazém',
            'quantity' => 'Quantidade',
            'reserved' => 'Reservado',
            'available' => 'Disponível',
        ],
        'filters' => [
            'warehouse' => 'Armazém',
        ],
    ],

    'warehouses' => [
        'fields' => [
            'code' => 'Código',
            'name' => 'Nome',
            'description' => 'Descrição',
        ],
        'columns' => [
            'created' => 'Criado',
            'updated' => 'Atualizado',
        ],
    ],

    'coupons' => [
        'fields' => [
            'code' => 'Código',
            'type' => 'Tipo',
            'value' => 'Valor',
            'min_cart' => 'Mínimo do carrinho',
            'max_discount' => 'Desconto máximo',
            'usage' => 'Uso',
            'usage_limit' => 'Limite total de uso',
            'per_user_limit' => 'Limite por usuário',
            'starts_at' => 'Início',
            'expires_at' => 'Expira em',
            'is_active' => 'Ativo',
        ],
        'filters' => [
            'is_active' => 'Ativo',
        ],
        'helpers' => [
            'code_unique' => 'Código único que os clientes irão inserir.',
        ],
        'types' => [
            'fixed' => 'Valor fixo',
            'percent' => 'Percentual',
        ],
    ],

    'reviews' => [
        'fields' => [
            'product' => 'Produto',
            'user' => 'Usuário',
            'rating' => 'Avaliação',
            'status' => 'Status',
            'text' => 'Texto da avaliação',
            'created_at' => 'Criado',
        ],
        'filters' => [
            'status' => 'Status',
        ],
        'statuses' => [
            'pending' => 'Pendente',
            'approved' => 'Aprovada',
            'rejected' => 'Rejeitada',
        ],
    ],

    'users' => [
        'fields' => [
            'points_balance' => 'Saldo de pontos',
            'password' => 'Senha',
            'roles' => 'Funções',
            'categories' => 'Categorias permitidas',
        ],
    ],

    'api' => [
        'common' => [
            'not_found' => 'Recurso não encontrado.',
        ],
        'auth' => [
            'unauthenticated' => 'Não autenticado.',
            'verification_link_sent' => 'Link de verificação enviado.',
            'two_factor_required' => 'É necessário o código de autenticação em duas etapas.',
            'invalid_two_factor_code' => 'Código de autenticação em duas etapas inválido.',
        ],
        'verify_email' => [
            'invalid_signature' => 'Assinatura inválida para verificação de e-mail.',
            'already_verified' => 'E-mail já verificado.',
            'verified' => 'E-mail verificado.',
        ],
        'cart' => [
            'not_enough_stock' => 'Estoque insuficiente',
            'coupon_not_found' => 'Cupom não encontrado.',
            'coupon_not_applicable' => 'O cupom não pode ser aplicado a este carrinho.',
            'points_auth_required' => 'Apenas usuários autenticados podem resgatar pontos de fidelidade.',
        ],
        'orders' => [
            'cart_empty' => 'Carrinho vazio',
            'insufficient_stock' => 'Estoque insuficiente para o produto nº :product',
            'coupon_unavailable' => 'O cupom não está mais disponível.',
            'coupon_usage_limit_reached' => 'Limite de uso do cupom atingido.',
            'not_enough_points' => 'Pontos de fidelidade insuficientes para resgatar o valor solicitado.',
            'points_redeemed_description' => 'Pontos resgatados para o pedido :number',
            'points_earned_description' => 'Pontos acumulados no pedido :number',
        ],
        'reviews' => [
            'submitted' => 'Avaliação enviada para moderação.',
        ],
        'payments' => [
            'missing_intent' => 'payment_intent ausente.',
            'invalid_signature' => 'Assinatura do Stripe inválida.',
        ],
    ],

    'loyalty' => [
        'transaction' => [
            'earn' => 'Acumulados :points pontos de fidelidade.',
            'redeem' => 'Resgatados :points pontos de fidelidade.',
            'adjustment' => 'Saldo ajustado em :points.',
        ],
        'demo' => [
            'checkout_redeem' => 'Pontos resgatados no checkout',
            'shipped_bonus' => 'Bônus pelo pedido enviado :number',
            'cancellation_return' => 'Pontos devolvidos após cancelamento',
        ],
        'transactions' => [
            'fields' => [
                'type' => 'Tipo',
                'points' => 'Pontos',
                'amount' => 'Valor',
                'description' => 'Descrição',
            ],
            'types' => [
                'earn' => 'Acúmulo',
                'redeem' => 'Resgate',
                'adjustment' => 'Ajuste',
            ],
        ],
    ],

    'widgets' => [
        'orders_stats' => [
            'labels' => [
                'new' => 'Novos',
                'paid' => 'Pagos',
                'shipped' => 'Enviados',
                'cancelled' => 'Cancelados',
            ],
            'descriptions' => [
                'new' => 'Aguardando',
            ],
        ],
    ],

    'products' => [
        'fields' => [
            'name' => 'Nome',
            'slug' => 'Slug',
            'description' => 'Descrição',
            'sku' => 'SKU',
            'category' => 'Categoria',
            'vendor' => 'Fornecedor',
            'preview' => 'Pré-visualização',
            'preview_url_debug' => 'URL?',
            'stock' => 'Estoque',
            'price' => 'Preço',
            'price_old' => 'Preço antigo',
            'is_active' => 'Ativo',
        ],
        'attributes' => [
            'label' => 'Atributos',
            'name' => 'Nome',
            'value' => 'Valor',
            'add' => 'Adicionar atributo',
            'translations' => 'Traduções',
        ],
        'placeholders' => [
            'available_stock' => 'Estoque disponível',
        ],
        'filters' => [
            'category' => 'Categoria',
            'is_active' => [
                'label' => 'Atividade',
                'true' => 'Ativos',
                'false' => 'Inativos',
            ],
        ],
        'images' => [
            'title' => 'Imagens',
            'fields' => [
                'image' => 'Imagem',
                'alt_text' => 'Texto alternativo',
                'is_primary' => 'Imagem principal',
                'preview' => 'Pré-visualização',
                'disk' => 'Disco',
                'sort' => 'Ordem',
                'created_at' => 'Criado em',
            ],
            'helper_texts' => [
                'is_primary' => 'Usada como pré-visualização do produto.',
            ],
            'actions' => [
                'create' => 'Adicionar',
                'edit' => 'Editar',
                'delete' => 'Excluir',
            ],
            'empty' => [
                'heading' => 'Ainda não há imagens',
                'description' => 'Adicione imagens do produto para vê-las aqui.',
            ],
        ],
    ],

    'categories' => [
        'fields' => [
            'name' => 'Nome',
            'slug' => 'Slug',
            'parent' => 'Categoria pai',
            'deleted_at' => 'Excluído em',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ],
    ],

    'vendor' => [
        'fields' => [
            'name' => 'Nome',
            'slug' => 'Slug',
            'description' => 'Descrição',
            'deleted_at' => 'Excluído em',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ],
    ],

    'currencies' => [
        'navigation_group' => 'Configurações',
        'code' => 'Código',
        'rate' => 'Taxa',
        'rate_vs_base' => 'Taxa (vs base)',
        'updated' => 'Atualizado',
    ],

    'security' => [
        'two_factor' => [
            'not_initialized' => 'A autenticação em duas etapas não está configurada.',
            'invalid_code' => 'Código de autenticação em duas etapas inválido.',
            'enabled' => 'Autenticação em duas etapas ativada.',
        ],
    ],
];
