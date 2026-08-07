<?php
return [
    // Navbar
    'nav.servicios'    => 'Services',
    'nav.metodologia'  => 'Methodology',
    'nav.normas'       => 'ISO Standards',
    'nav.equipo'       => 'Team',
    'nav.contacto'     => 'Contact',
    'nav.evaluacion'   => 'Risk Assessment',
    'nav.toggler'      => 'Open menu',
    'nav.cta'          => 'Request a diagnosis',

    // Footer
    'footer.tagline'   => 'Information security risk management powered by Artificial Intelligence, aligned with ISO/IEC 27001, 27002 and 27007.',
    'footer.nav'       => 'Navigation',
    'footer.equipo'    => 'Consulting team',
    'footer.contacto'  => 'Contact',
    'footer.copyright' => 'RiskGuard Consulting. Academic project — all rights reserved.',

    'home.pagetitle' => 'Home',
    'meta.description' => 'Specialized consulting in information security risk management, ISO/IEC 27001, 27002 and 27007 compliance, powered by Artificial Intelligence.',

    // Home - hero
    'home.eyebrow'          => 'Information security consulting',
    'home.hero.title1'      => 'AI doesn\'t replace the',
    'home.hero.title2'      => 'risk manager.',
    'home.hero.title3'      => 'It makes them more precise.',
    'home.hero.lead'        => 'We help organizations identify, assess and treat their information security risks, supporting compliance with <strong>ISO/IEC 27001</strong>, <strong>27002</strong> and <strong>27007</strong>, using Artificial Intelligence as a support tool while the manager always remains in charge of the decision.',
    'home.hero.btn.evaluar' => 'Run a risk assessment',

    'home.matrix.title'    => 'Risk matrix',
    'home.matrix.subtitle' => 'probability × impact',
    'home.matrix.low'      => 'Low',
    'home.matrix.mid'      => 'Medium',
    'home.matrix.high'     => 'High',
    'home.matrix.crit'     => 'Critical',

    // Home - servicios
    'home.servicios.eyebrow' => 'Services',
    'home.servicios.title'   => 'Where human judgment leads and AI accelerates',
    'home.servicios.s1.title' => 'Risk identification and assessment',
    'home.servicios.s1.desc'  => 'Assessment of assets, threats and vulnerabilities, with AI models that prioritize the highest probability and impact risks to speed up the team\'s analysis.',
    'home.servicios.s2.title' => 'ISMS implementation (ISO/IEC 27001)',
    'home.servicios.s2.desc'  => 'Design and implementation of the Information Security Management System, applying the PDCA cycle and the ISO/IEC 27002 controls.',
    'home.servicios.s3.title' => 'Internal audit (ISO/IEC 27007)',
    'home.servicios.s3.desc'  => 'Planning and execution of ISMS audits, with AI support in reviewing documentary evidence and detecting nonconformities.',

    // Home - metodología
    'home.metodologia.eyebrow' => 'Methodology',
    'home.metodologia.title'   => 'Risk management cycle',
    'home.metodologia.c1.title' => 'Identification',
    'home.metodologia.c1.desc'  => 'Recognition of information assets, threats and vulnerabilities.',
    'home.metodologia.c2.title' => 'Analysis and assessment',
    'home.metodologia.c2.desc'  => 'Estimation of probability and impact to prioritize critical risks.',
    'home.metodologia.c3.title' => 'Treatment',
    'home.metodologia.c3.desc'  => 'Mitigation, transfer, acceptance or avoidance, depending on the case.',
    'home.metodologia.c4.title' => 'Monitoring and review',
    'home.metodologia.c4.desc'  => 'Verification of controls and continuous adjustment against new risks.',

    // Home - normas
    'home.normas.eyebrow' => 'Regulatory framework',
    'home.normas.title'   => 'ISO/IEC standards that guide our work',
    'home.normas.n1.desc' => 'Requirements for establishing, implementing, maintaining and improving an ISMS.',
    'home.normas.n2.desc' => 'Guidelines and catalog of information security controls.',
    'home.normas.n3.desc' => 'Guidance for auditing information security management systems.',

    // Home - equipo
    'home.equipo.eyebrow' => 'Consulting team',
    'home.equipo.title'   => 'Who is behind RiskGuard',

    // Home - contacto
    'home.contacto.eyebrow' => 'Contact',
    'home.contacto.title'   => 'Let\'s talk about your organization\'s risks',
    'home.contacto.lead'    => 'Write to us and we\'ll get in touch to schedule an initial diagnosis.',
    'home.contacto.ph.nombre'   => 'Name',
    'home.contacto.ph.email'    => 'Email address',
    'home.contacto.ph.org'      => 'Organization',
    'home.contacto.ph.mensaje'  => 'Briefly tell us what you need',
    'home.contacto.btn'         => 'Send message',

    // Evaluación - intro
    'eval.pagetitle' => 'Risk Assessment',
    'eval.eyebrow'    => 'Assessment module',
    'eval.title'      => 'Risk assessment for your database',
    'eval.lead'       => 'Questionnaire based on <strong>ISO/IEC 27002</strong>, <strong>ISO/IEC 27005</strong> and <strong>COBIT</strong> best practices. Answer each control according to its current compliance level; when you finish you will get a dashboard with the risk index for the <strong>Confidentiality · Integrity · Availability</strong> triad and improvement recommendations.',

    // Evaluación - datos generales
    'eval.meta.title'      => 'General information',
    'eval.meta.org'        => 'Organization name',
    'eval.meta.evaluador'  => 'Evaluator name',
    'eval.meta.fecha'      => 'Date',

    // Evaluación - preguntas / envío
    'eval.ayuda.label'         => 'View question explanation',
    'eval.nivel.label'         => 'Maturity level (0-5)',
    'eval.comentario.btn'      => 'Add comment',
    'eval.comentario.placeholder' => 'Write here why it does not apply or why the level is 0...',
    'eval.btn.calcular'        => 'Calculate results',
    'eval.hint.responder'      => 'You must answer all 26 questions to see the results dashboard.',
    'eval.btn.pdf'             => 'Download PDF',
    'eval.btn.excel'           => 'Download Excel',

    // Evaluación - dashboard
    'eval.dashboard.eyebrow' => 'Results',
    'eval.dashboard.title'   => 'Assessment dashboard',
    'eval.global.label'      => 'Overall security index',
    'eval.chart.dimension'   => 'Compliance by dimension (C · I · A)',
    'eval.chart.global'      => 'Overall compliance level',
    'eval.chart.preguntas'   => 'Maturity level per question (self-assessed)',
    'eval.recos.title'       => 'Improvement recommendations',
    'eval.weak.title'        => 'Weakest controls detected',

    // JS (embedded via JSON in evaluacion-riesgos.php)
    'js.alert.incompleto'   => 'Please answer all questions before calculating the results.',
    'js.alert.comentario'   => 'Please fill in the required comment for questions marked "N/A" or with maturity level 0.',
    'js.status.guardando'   => 'Saving assessment...',
    'js.status.guardado'    => 'Assessment saved successfully to the database.',
    'js.status.error'       => 'Could not save to the database (results are still visible below).',
    'js.fallback.sinbrechas' => 'No significant gaps were detected: it is recommended to keep periodic monitoring of the current controls.',
    'js.fallback.madurezok'  => 'All evaluated controls show an adequate maturity level.',
    'js.chart.pctcumplimiento' => '% compliance',
    'js.chart.cumplimiento'    => 'Compliance',
    'js.chart.brecha'          => 'Gap',
    'js.chart.nivelmadurez'    => 'Maturity level (0-5)',
    'js.madurez.label'         => 'Maturity',
    'js.semaforo.verde'        => 'Green',
    'js.semaforo.amarillo'     => 'Yellow',
    'js.semaforo.rojo'         => 'Red',
    'js.semaforo.verde.texto'    => 'Adequate level of control implementation.',
    'js.semaforo.amarillo.texto' => 'There are opportunities for improvement.',
    'js.semaforo.rojo.texto'     => 'High risk level and need for corrective actions.',

    // PDF / Excel export
    'js.export.titulo'        => 'Risk assessment report',
    'js.export.organizacion'  => 'Organization',
    'js.export.evaluador'     => 'Evaluator',
    'js.export.fecha'         => 'Date',
    'js.export.global'        => 'Overall security index',
    'js.export.dimension'     => 'Dimension',
    'js.export.id'            => 'ID',
    'js.export.control'       => 'Control',
    'js.export.pregunta'      => 'Question',
    'js.export.respuesta'     => 'Answer',
    'js.export.nivel'         => 'Maturity level',
    'js.export.nivelauto'     => 'Automatic level',
    'js.export.comentario'    => 'Comment',
];
