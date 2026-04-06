import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

const INDIGO = [99, 102, 241]
const DARK_BG = [15, 15, 25]
const LIGHT_TEXT = [226, 232, 240]

function setupDoc(title) {
  const doc = new jsPDF()
  // Header bar
  doc.setFillColor(...INDIGO)
  doc.rect(0, 0, 210, 20, 'F')
  doc.setTextColor(255, 255, 255)
  doc.setFontSize(14)
  doc.setFont('helvetica', 'bold')
  doc.text('FinTrack', 14, 13)
  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.text(title, 196, 13, { align: 'right' })

  // Date
  doc.setTextColor(150, 150, 150)
  doc.setFontSize(8)
  doc.text(`Generated: ${new Date().toLocaleDateString('en-GB')}`, 14, 28)

  return doc
}

function formatCurrency(val) {
  return parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

/**
 * Export monthly report to PDF.
 */
export function exportMonthlyPdf(data, monthName, year) {
  const doc = setupDoc(`Monthly Report — ${monthName} ${year}`)
  let y = 35

  // KPI row
  doc.setFontSize(11)
  doc.setTextColor(60, 60, 60)
  doc.setFont('helvetica', 'bold')
  doc.text(`${monthName} ${year} Summary`, 14, y)
  y += 8

  doc.setFontSize(9)
  doc.setFont('helvetica', 'normal')
  const kpis = [
    ['Income', formatCurrency(data.totals.income)],
    ['Expenses', formatCurrency(data.totals.expense)],
    ['Balance', formatCurrency(data.totals.balance)],
    ['Savings Rate', `${(data.totals.savings_rate || 0).toFixed(1)}%`],
  ]
  kpis.forEach(([label, value], i) => {
    doc.text(`${label}: ${value}`, 14 + (i * 48), y)
  })
  y += 10

  // Expense categories table
  if (data.expense_categories?.length) {
    doc.setFont('helvetica', 'bold')
    doc.text('Expense Categories', 14, y)
    y += 2

    autoTable(doc, {
      startY: y,
      head: [['Category', 'Transactions', 'Amount', 'Share']],
      body: data.expense_categories.map(c => [
        c.category_name,
        c.count || '—',
        formatCurrency(c.total),
        `${(c.share || 0).toFixed(1)}%`,
      ]),
      theme: 'striped',
      headStyles: { fillColor: INDIGO, fontSize: 8 },
      bodyStyles: { fontSize: 8 },
      margin: { left: 14, right: 14 },
    })
    y = doc.lastAutoTable.finalY + 10
  }

  // Income categories table
  if (data.income_categories?.length) {
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)
    doc.text('Income Categories', 14, y)
    y += 2

    autoTable(doc, {
      startY: y,
      head: [['Category', 'Transactions', 'Amount', 'Share']],
      body: data.income_categories.map(c => [
        c.category_name,
        c.count || '—',
        formatCurrency(c.total),
        `${(c.share || 0).toFixed(1)}%`,
      ]),
      theme: 'striped',
      headStyles: { fillColor: [16, 185, 129], fontSize: 8 },
      bodyStyles: { fontSize: 8 },
      margin: { left: 14, right: 14 },
    })
  }

  doc.save(`fintrack_monthly_${year}_${String(data.period.month).padStart(2, '0')}.pdf`)
}

/**
 * Export yearly report to PDF.
 */
export function exportYearlyPdf(data, year) {
  const doc = setupDoc(`Yearly Report — ${year}`)
  let y = 35

  doc.setFontSize(11)
  doc.setTextColor(60, 60, 60)
  doc.setFont('helvetica', 'bold')
  doc.text(`${year} Summary`, 14, y)
  y += 8

  doc.setFontSize(9)
  doc.setFont('helvetica', 'normal')
  doc.text(`Income: ${formatCurrency(data.totals.income)}`, 14, y)
  doc.text(`Expenses: ${formatCurrency(data.totals.expense)}`, 80, y)
  doc.text(`Balance: ${formatCurrency(data.totals.balance)}`, 146, y)
  y += 10

  // Monthly trend table
  if (data.monthly_trend?.length) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    const incomeByMonth = {}
    const expenseByMonth = {}
    data.monthly_trend.forEach(t => {
      if (t.type === 'income') incomeByMonth[t.month] = parseFloat(t.total)
      else expenseByMonth[t.month] = parseFloat(t.total)
    })

    doc.setFont('helvetica', 'bold')
    doc.text('Monthly Trend', 14, y)
    y += 2

    autoTable(doc, {
      startY: y,
      head: [['Month', 'Income', 'Expenses', 'Balance']],
      body: months.map((m, i) => {
        const inc = incomeByMonth[i + 1] || 0
        const exp = expenseByMonth[i + 1] || 0
        return [m, formatCurrency(inc), formatCurrency(exp), formatCurrency(inc - exp)]
      }),
      theme: 'striped',
      headStyles: { fillColor: INDIGO, fontSize: 8 },
      bodyStyles: { fontSize: 8 },
      margin: { left: 14, right: 14 },
    })
    y = doc.lastAutoTable.finalY + 10
  }

  // Expense categories
  if (data.expense_categories?.length) {
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)
    doc.text('Expense Categories', 14, y)
    y += 2

    autoTable(doc, {
      startY: y,
      head: [['Category', 'Transactions', 'Amount', 'Share']],
      body: data.expense_categories.map(c => [
        c.category_name,
        c.count || '—',
        formatCurrency(c.total),
        `${(c.share || 0).toFixed(1)}%`,
      ]),
      theme: 'striped',
      headStyles: { fillColor: INDIGO, fontSize: 8 },
      bodyStyles: { fontSize: 8 },
      margin: { left: 14, right: 14 },
    })
  }

  doc.save(`fintrack_yearly_${year}.pdf`)
}

/**
 * Export income vs expense comparison to PDF.
 */
export function exportComparisonPdf(data, year) {
  const doc = setupDoc(`Income vs Expense — ${year}`)
  let y = 35

  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']

  autoTable(doc, {
    startY: y,
    head: [['Month', 'Income', 'Expenses', 'Balance']],
    body: data.map(r => [
      months[r.month - 1],
      formatCurrency(r.income),
      formatCurrency(r.expense),
      formatCurrency(r.balance),
    ]),
    theme: 'striped',
    headStyles: { fillColor: INDIGO, fontSize: 8 },
    bodyStyles: { fontSize: 8 },
    margin: { left: 14, right: 14 },
  })

  // Totals row
  const totals = data.reduce((acc, r) => ({
    income: acc.income + parseFloat(r.income),
    expense: acc.expense + parseFloat(r.expense),
    balance: acc.balance + parseFloat(r.balance),
  }), { income: 0, expense: 0, balance: 0 })

  const finalY = doc.lastAutoTable.finalY + 8
  doc.setFont('helvetica', 'bold')
  doc.setFontSize(9)
  doc.text(`Totals — Income: ${formatCurrency(totals.income)}  |  Expenses: ${formatCurrency(totals.expense)}  |  Net: ${formatCurrency(totals.balance)}`, 14, finalY)

  doc.save(`fintrack_comparison_${year}.pdf`)
}

/**
 * Export budget performance to PDF.
 */
export function exportBudgetPerfPdf(data) {
  const doc = setupDoc(`Budget Performance — ${data.year}`)
  let y = 35

  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']

  // Summary
  doc.setFontSize(9)
  doc.setFont('helvetica', 'normal')
  doc.setTextColor(60, 60, 60)
  doc.text(`Budgeted: ${formatCurrency(data.summary.total_budgeted)}  |  Spent: ${formatCurrency(data.summary.total_spent)}  |  Usage: ${data.summary.overall_percentage}%  |  Over budget: ${data.summary.over_budget_count}/${data.summary.total_budgets}`, 14, y)
  y += 8

  // Monthly tables
  for (const month of data.months) {
    if (y > 260) {
      doc.addPage()
      y = 20
    }

    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)
    doc.text(months[month.month - 1], 14, y)
    y += 2

    autoTable(doc, {
      startY: y,
      head: [['Category', 'Budgeted', 'Spent', 'Remaining', 'Usage']],
      body: month.budgets.map(b => [
        b.category_name || 'General',
        formatCurrency(b.amount),
        formatCurrency(b.spent),
        formatCurrency(b.remaining),
        `${(b.percentage || 0).toFixed(0)}%`,
      ]),
      theme: 'striped',
      headStyles: { fillColor: INDIGO, fontSize: 7 },
      bodyStyles: { fontSize: 7 },
      margin: { left: 14, right: 14 },
    })
    y = doc.lastAutoTable.finalY + 8
  }

  doc.save(`fintrack_budget_performance_${data.year}.pdf`)
}
